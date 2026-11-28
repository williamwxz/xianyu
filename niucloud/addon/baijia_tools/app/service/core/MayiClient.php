<?php
namespace addon\baijia_tools\app\service\core;

use GuzzleHttp\Client;
use RuntimeException;

class MayiClient
{
    private const METHODS = ['recharge' => 'rerechargeorder', 'dining' => 'dcorder', 'movie' => 'movieorder'];
    private const STATUS = [
        'dining' => [0=>'待付款', 1=>'已付款待出餐', 2=>'出餐中', 3=>'出餐成功', 4=>'已完成', 5=>'出餐失败退款', 10=>'订单关闭'],
        'movie' => [1=>'已付款', 2=>'受理中', 3=>'待出票', 4=>'已出票', 5=>'已结算', 6=>'待付款', 10=>'订单关闭'],
        'recharge' => [0=>'待付款', 1=>'已付款', 2=>'充值中', 3=>'充值完成', 5=>'已结算', 10=>'订单关闭'],
    ];

    public static function sign(array $params, string $secret): string
    {
        unset($params['apikey'], $params['sign']);
        ksort($params, SORT_STRING);
        $parts = ['secret=' . $secret];
        foreach ($params as $key => $value) {
            if ($value !== '' && $value !== null) $parts[] = $key . '=' . $value;
        }
        $parts[] = 'secret=' . $secret;
        return md5(implode('&', $parts));
    }

    public static function orders(string $kind, int $page, string $uid, string $key, string $secret): array
    {
        if (!isset(self::METHODS[$kind])) throw new RuntimeException('未知订单类型');
        $params = ['page'=>$page, 'limit'=>20, 'uid'=>$uid];
        $params['sign'] = self::sign($params, $secret);
        $params['apikey'] = $key;
        try {
            $url = 'https://api-gw.haojingke.com/index.php/v2/api/open/' . self::METHODS[$kind];
            $response = (new Client(['timeout'=>10, 'connect_timeout'=>4, 'allow_redirects'=>false]))->post($url, ['form_params'=>$params]);
            $body = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (\Throwable $e) {
            throw new RuntimeException('暂时无法连接蚂蚁星球，请稍后重试');
        }
        if ((string)($body['status_code'] ?? '') !== '200' || !is_array($body['data']['list'] ?? null)) {
            throw new RuntimeException('蚂蚁星球查单失败，请核对商户配置');
        }
        return self::memberPage($body['data'], $kind, $uid, $page);
    }

    public static function memberPage(array $data, string $kind, string $uid, int $page): array
    {
        $orders = [];
        foreach ($data['list'] as $row) {
            if ($kind === 'movie' && preg_match('/^BQ[0-9a-f]{24}$/D',(string)($row['third_orderid'] ?? ''))) continue;
            if ((string)($row['uid'] ?? '') !== $uid || empty($row['orderid'])) continue;
            $amount = $row['payprice'] ?? null;
            $orders[] = [
                'id'=>(string)$row['orderid'],
                'title'=> $row['storeName'] ?? $row['goods_name'] ?? ($row['info'][3] ?? '电影票'),
                'status'=>self::STATUS[$kind][$row['status'] ?? ''] ?? '状态待确认',
                'amount'=>is_numeric($amount) ? number_format((float)$amount / ($kind === 'dining' ? 1 : 100), 2, '.', '') : null,
                'pickup'=>$kind === 'dining' ? (string)($row['takeCode'] ?? '') : '',
            ];
        }
        return ['list'=>$orders, 'has_more'=>isset($data['total']) ? $page * 20 < (int)$data['total'] : count($data['list']) === 20];
    }
}
