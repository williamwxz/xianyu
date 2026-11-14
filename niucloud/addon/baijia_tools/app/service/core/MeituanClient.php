<?php
namespace addon\baijia_tools\app\service\core;

use GuzzleHttp\Client;
use RuntimeException;

/** Meituan's signed POST API. Never pass credentials or upstream exceptions to clients. */
class MeituanClient
{
    public static function headers(string $path, string $body, string $key, string $secret, string $timestamp): array
    {
        $md5 = base64_encode(md5($body, true));
        $canonical = "POST\n$md5\nS-Ca-App:$key\nS-Ca-Timestamp:$timestamp\n$path";
        return ['Content-Type' => 'application/json', 'Content-MD5' => $md5,
            'S-Ca-App' => $key, 'S-Ca-Timestamp' => $timestamp,
            'S-Ca-Signature-Headers' => 'S-Ca-App,S-Ca-Timestamp',
            'S-Ca-Signature' => base64_encode(hash_hmac('sha256', $canonical, $secret, true))];
    }

    public function call(string $method, array $params): array
    {
        $key = (string)env('MEITUAN.APPKEY', '');
        $secret = (string)env('MEITUAN.APPSECRET', '');
        if ($key === '' || $secret === '') throw new RuntimeException('美团服务尚未配置');
        $path = '/cps_open/common/api/v1/' . $method;
        $body = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        try {
            $response = (new Client(['timeout' => 12, 'connect_timeout' => 5]))->post('https://media.meituan.com' . $path, [
                'headers' => self::headers($path, $body, $key, $secret, (string)(int)floor(microtime(true) * 1000)), 'body' => $body
            ]);
            $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new RuntimeException('美团服务暂不可用，请稍后重试');
        }
        if (!is_array($data) || !isset($data['code']) || (string)$data['code'] !== '0') {
            throw new RuntimeException('美团商品暂不可用，请稍后重试');
        }
        return $data;
    }

    public static function product(array $item): ?array
    {
        $d = $item['couponPackDetail'] ?? [];
        if (empty($d['productViewSign']) || empty($d['name']) || ($d['saleStatus'] ?? true) === false || !is_numeric($d['sellPrice'] ?? null)) return null;
        $platform = (int)($d['platform'] ?? 0);
        if (!in_array($platform, [1, 2], true)) return null;
        $image = preg_replace('#^http://#', 'https://', (string)($d['headUrl'] ?? ''));
        return ['id' => (string)$d['productViewSign'], 'name' => (string)$d['name'],
            'image' => str_starts_with($image, 'https://') ? $image : '',
            'price' => number_format((float)$d['sellPrice'], 2, '.', ''),
            'original_price' => is_numeric($d['originalPrice'] ?? null) ? number_format((float)$d['originalPrice'], 2, '.', '') : '',
            'sales' => (string)($d['saleVolume'] ?? ''),
            'stores' => isset($item['availablePoiInfo']['availablePoiNum']) ? (int)$item['availablePoiInfo']['availablePoiNum'] : null,
            'platform' => $platform, 'biz_line' => isset($d['bizLine']) ? (int)$d['bizLine'] : null];
    }
}
