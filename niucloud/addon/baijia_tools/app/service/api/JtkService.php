<?php
namespace addon\baijia_tools\app\service\api;

use addon\baijia_tools\app\dict\JtkActDict;
use core\base\BaseApiService;
use core\exception\ApiException;
use GuzzleHttp\Client;
use think\facade\Cache;

/**
 * 聚推客联盟代理。apikey 只在服务器 .env 的 [JUTUIKE] APIKEY，不下发到前端。
 */
class JtkService extends BaseApiService
{
    private const BASE = 'https://api.jutuike.com/union/';

    /** 配置中所有活动的展示信息（名称、官方图标/样图），缓存 1 小时 */
    public function acts(): array
    {
        return Cache::remember('baijia_jtk_acts', function () {
            $remote = [];
            for ($page = 1; $page <= 3; $page++) {
                $data = $this->call('act_list', ['page' => $page, 'pageSize' => 100]);
                foreach ($data['data'] ?? [] as $act) $remote[(int)$act['act_id']] = $act;
                if ($page >= (int)($data['last_page'] ?? 1)) break;
            }
            $list = [];
            foreach (JtkActDict::ACTS as $key => $item) {
                $act = $remote[$item['act_id']] ?? null;
                $list[$key] = [
                    'key' => $key,
                    'name' => $item['name'],
                    'available' => $act !== null,
                    'title' => $act['act_name'] ?? '',
                    'desc' => $act['desc'] ?? '',
                    'icon' => $act['icon'] ?? '',
                    'img' => $act['img'] ?? '',
                ];
            }
            return $list;
        }, 3600);
    }

    /** 转链：sid 记录站点和会员，用于之后的订单归属与分佣 */
    public function link(string $key): array
    {
        $item = JtkActDict::get($key);
        if (!$item) throw new ApiException('活动不存在');
        $sid = 's' . (int)$this->site_id . ($this->member_id ? 'm' . (int)$this->member_id : 'g');
        return Cache::remember('baijia_jtk_link_' . $key . '_' . $sid, function () use ($item, $sid) {
            $data = $this->call('act', ['act_id' => $item['act_id'], 'sid' => $sid]);
            $weapp = $data['we_app_info'] ?? [];
            return [
                'name' => $data['act_name'] ?? $item['name'],
                'weapp' => !empty($weapp['app_id']) ? ['app_id' => $weapp['app_id'], 'page_path' => $weapp['page_path'] ?? ''] : null,
                'h5' => $data['h5'] ?? ($data['long_h5'] ?? ''),
            ];
        }, 1800);
    }

    private function call(string $path, array $query): array
    {
        $apikey = (string)env('JUTUIKE.APIKEY', '');
        if ($apikey === '') throw new ApiException('聚推客未配置');
        try {
            $response = (new Client(['timeout' => 8]))->get(self::BASE . $path, ['query' => array_merge($query, ['apikey' => $apikey])]);
            $body = json_decode((string)$response->getBody(), true);
        } catch (\Throwable $e) {
            throw new ApiException('活动服务暂时不可用，请稍后再试');
        }
        if (!is_array($body) || (int)($body['code'] ?? 0) !== 1) {
            // 例如“该活动暂停推广”
            throw new ApiException(trim((string)($body['msg'] ?? '活动暂不可用'), '【】') ?: '活动暂不可用');
        }
        return is_array($body['data'] ?? null) ? $body['data'] : [];
    }
}
