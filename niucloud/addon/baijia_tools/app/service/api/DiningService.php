<?php
namespace addon\baijia_tools\app\service\api;

use app\service\core\sys\CoreConfigService;
use core\base\BaseApiService;
use core\exception\ApiException;

class DiningService extends BaseApiService
{
    public function catalog(string $brand, string $city, string $storeId): array
    {
        $catalog = (new CoreConfigService())->getConfigValue($this->site_id, 'BAIJIA_DINING_CATALOG');
        $stores = $catalog[$brand][$city] ?? [];
        $list = [];
        foreach ($stores as $row) {
            $store = [
                'id' => (string)$row['id'], 'name' => (string)$row['name'],
                'address' => (string)$row['address'], 'open' => (bool)$row['open'],
            ];
            if ($storeId === '') {
                $list[] = $store;
                continue;
            }
            if ($storeId !== $store['id']) continue;
            $items = [];
            foreach ($row['items'] ?? [] as $sku) {
                if (!is_int($sku['price_fen'] ?? null) || $sku['price_fen'] <= 0) continue;
                $items[] = [
                    'id' => (string)$sku['id'], 'name' => (string)$sku['name'],
                    'spec' => (string)($sku['spec'] ?? ''), 'price_fen' => $sku['price_fen'],
                    'stock' => max(0, (int)($sku['stock'] ?? 0)),
                ];
            }
            return ['store' => $store, 'items' => $items];
        }
        if ($storeId !== '') throw new ApiException('门店已下线，请重新选择');
        return ['stores' => $list];
    }
}
