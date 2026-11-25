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
        foreach (is_array($stores) ? $stores : [] as $row) {
            if (!self::hasIdAndName($row)) continue;
            $store = [
                'id' => (string)$row['id'], 'name' => (string)$row['name'],
                'address' => is_string($row['address'] ?? null) ? $row['address'] : '', 'open' => in_array($row['open'] ?? false, [true, 1, '1'], true),
            ];
            if ($storeId === '') {
                $list[] = $store;
                continue;
            }
            if ($storeId !== $store['id']) continue;
            $items = [];
            $menu = $row['items'] ?? [];
            foreach (is_array($menu) ? $menu : [] as $sku) {
                if (!self::hasIdAndName($sku)) continue;
                if (!is_int($sku['price_fen'] ?? null) || $sku['price_fen'] <= 0) continue;
                $items[] = [
                    'id' => (string)$sku['id'], 'name' => (string)$sku['name'],
                    'spec' => is_string($sku['spec'] ?? null) ? $sku['spec'] : '', 'price_fen' => $sku['price_fen'],
                    'stock' => is_numeric($sku['stock'] ?? null) ? max(0, (int)$sku['stock']) : 0,
                ];
            }
            return ['store' => $store, 'items' => $items];
        }
        if ($storeId !== '') throw new ApiException('门店已下线，请重新选择');
        return ['stores' => $list];
    }

    private static function hasIdAndName($row): bool
    {
        if (!is_array($row)) return false;
        $id = $row['id'] ?? null;
        return (is_string($id) || is_int($id)) && trim((string)$id) !== ''
            && is_string($row['name'] ?? null) && trim($row['name']) !== '';
    }
}
