<?php
namespace addon\baijia_tools\app\service\api;

use addon\baijia_tools\app\service\core\MeituanClient;
use core\base\BaseApiService;
use core\exception\ApiException;
use think\facade\Cache;

class MeituanService extends BaseApiService
{
    private function call(string $method, array $params): array
    {
        try { return (new MeituanClient())->call($method, $params); }
        catch (\RuntimeException $e) { throw new ApiException($e->getMessage()); }
    }

    public function products(array $input): array
    {
        $city = (string)$input['city_id'];
        $cities = json_decode(file_get_contents(dirname(__DIR__, 3) . '/uni-app/lib/meituan-cities.json'), true);
        if (!in_array($city, array_column($cities, 'id'), true)) throw new ApiException('请选择城市');
        $keyword = trim((string)$input['keyword']);
        if (mb_strlen($keyword) > 100) throw new ApiException('搜索词不能超过100字');
        $cursor = (string)$input['cursor'];
        if (strlen($cursor) > 4096) throw new ApiException('分页参数无效');
        $params = ['cityId' => $city];
        if ($keyword !== '') {
            // Search is cross-category; never send listTopiId/platform/bizLine with it.
            $params += ['searchText' => $keyword, 'pageSize' => 20];
            if ($cursor !== '') $params['searchId'] = $cursor;
        } else {
            $topic = (int)$input['topic'];
            if (!in_array($topic, [2, 3, 5], true)) throw new ApiException('榜单参数无效');
            $params += ['platform' => 2, 'bizLine' => 1, 'listTopiId' => $topic];
        }
        $key = 'baijia_mt_goods_' . hash('sha256', json_encode($params));
        return Cache::remember($key, function () use ($params, $keyword) {
            $result = $this->call('query_coupon', $params);
            $items = [];
            foreach ($result['data'] ?? [] as $row) {
                if (!is_array($row)) continue;
                $product = MeituanClient::product($row);
                if ($product !== null) $items[] = $product;
            }
            $cursor = $keyword !== '' ? (string)($result['searchId'] ?? '') : '';
            return ['items' => $items, 'cursor' => $cursor,
                'has_more' => $keyword !== '' && !empty($result['hasNext']) && $cursor !== ''];
        }, 120);
    }

    public function link(array $input): array
    {
        $id = (string)$input['id'];
        $platform = (int)$input['platform'];
        $biz = $input['biz_line'] === '' ? null : (int)$input['biz_line'];
        if (!preg_match('/^[A-Za-z0-9_]{1,200}$/D', $id) || !in_array($platform, [1, 2], true)
            || ($platform === 2 && !in_array($biz, [1, 2, 3, 4], true))
            || ($platform === 1 && !in_array($biz, [null, 1, 5], true))) throw new ApiException('商品参数无效');
        // Only server-authenticated member identity can determine order attribution.
        $sid = 's' . (int)$this->site_id . ($this->member_id ? 'm' . (int)$this->member_id : 'g');
        $params = ['productViewSign' => $id, 'platform' => $platform, 'sid' => $sid, 'linkTypeList' => [1, 4]];
        if ($biz !== null) $params['bizLine'] = $biz;
        return Cache::remember('baijia_mt_link_' . hash('sha256', json_encode($params)), function () use ($params) {
            $r = $this->call('get_referral_link', $params);
            $links = $r['referralLinkMap'] ?? [];
            $path = (string)($links['4'] ?? '');
            $h5 = (string)($links['1'] ?? '');
            if ($path === '' && $h5 === '') throw new ApiException('商品暂不可购买');
            return ['weapp' => $path !== '' ? ['app_id' => 'wxde8ac0a21135c07d', 'page_path' => ltrim($path, '/')] : null,
                'h5' => str_starts_with($h5, 'https://') ? $h5 : ''];
        }, 300);
    }
}
