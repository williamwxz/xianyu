<?php
namespace addon\baijia_tools\app\service\api;

use addon\baijia_tools\app\service\core\KfcCity;
use core\base\BaseApiService;
use core\exception\ApiException;
use GuzzleHttp\Client;
use think\facade\Cache;

class NativeCatalogService extends BaseApiService
{
    private function read(string $method, array $params): array
    {
        $key = (string)env('HAOJINGKE.APIKEY', '');
        if (!$key || (int)$this->site_id !== (int)env('HAOJINGKE.SITE_ID', 0)) {
            throw new ApiException('本站暂未开通该服务');
        }
        try {
            $response = (new Client(['timeout' => 10, 'connect_timeout' => 4, 'allow_redirects' => false]))
                ->post('https://api-gw.haojingke.com/index.php/v2/api/' . $method, ['query' => ['apikey' => $key] + $params]);
            $body = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (\Throwable $e) {
            throw new ApiException('供应商暂时无法连接，请重试');
        }
        if ((string)($body['status_code'] ?? '') !== '200') throw new ApiException('供应商暂未提供该数据，请稍后重试');
        if (($body['data'] ?? null) === '' || ($body['data'] ?? null) === null) return [];
        if (!is_array($body['data'])) throw new ApiException('供应商数据格式有误');
        return $body['data'];
    }

    private function cityId(string $name, bool $movie): ?int
    {
        $method = $movie ? 'movie/citylist' : 'diancan/getcity';
        $cities = Cache::remember('baijia_city_v1_' . $this->site_id . ($movie ? '_movie' : '_kfc'), function () use ($method) {
            $rows = $this->read($method, []);
            $cities = [];
            foreach ($rows['list'] ?? $rows as $row) {
                $city = $row['cityName'] ?? $row['regionName'] ?? '';
                if ($city !== '' && !empty($row['cityId'])) $cities[preg_replace('/市$/u', '', $city)] = (int)$row['cityId'];
            }
            if (!$cities) throw new ApiException('城市目录暂不可用');
            return $cities;
        }, 3600);
        return $cities[preg_replace('/市$/u', '', trim($name))] ?? null;
    }

    private function store(array $row): array
    {
        return ['id' => (string)$row['storeCode'], 'name' => (string)$row['name'],
            'address' => $row['address'] ?? '', 'open' => in_array($row['officialStatus'] ?? false, [true, 1, '1'], true)];
    }

    private function kfcStores(KfcCity $directory, string $city, int $id): array
    {
        $key = 'baijia_kfc_stores_v2_' . $this->site_id . '_' . $id . '_' . $city;
        return Cache::remember($key, function () use ($directory, $city, $id) {
            $rows = $this->read('diancan/kfcstorenear', ['cityId' => $id, 'keywords' => '']);
            $stores = [];
            foreach ($rows as $row) {
                if (empty($row['storeCode']) || empty($row['name']) || !$directory->matches($row, $city, $id)) continue;
                $stores[] = $this->store($row);
            }
            usort($stores, static fn($a, $b) => $b['open'] <=> $a['open']);
            return $stores;
        }, 60);
    }

    public function kfc(string $city, string $store): array
    {
        $directory = new KfcCity();
        $city = $directory->name($city);
        $id = $city === '' ? null : $this->cityId($city, false);
        $stores = $id ? $this->kfcStores($directory, $city, $id) : [];
        if ($store === '') return ['stores' => $stores];
        if (!preg_match('/^[A-Za-z0-9_-]{1,40}$/D', $store)) throw new ApiException('门店编号有误');
        if ($city !== '' && !in_array($store, array_column($stores, 'id'), true)) throw new ApiException('该门店不在当前城市，请重新选择门店');
        $row = $this->read('diancan/kfcgoods', ['storeCode' => $store]);
        if ((string)($row['storeCode'] ?? '') !== $store || empty($row['name'])) throw new ApiException('该门店菜单暂不可用');
        if ($id && !$directory->matches($row, $city, $id)) throw new ApiException('该门店地址与当前城市不符，请重新选择门店');
        $items = [];
        $groups = $row['menu'] ?? [];
        while ($groups) {
            $group = array_shift($groups);
            foreach ($group['childClassList'] ?? [] as $child) $groups[] = $child;
            foreach ($group['menuList'] ?? [] as $item) {
                $id = (string)($item['linkId'] ?? '');
                $price = $item['price'] ?? null;
                if (!$id || empty($item['nameCn']) || !is_numeric($price) || $price <= 0) continue;
                $items[$id] = ['id' => $id, 'name' => $item['nameCn'], 'spec' => $group['topName'] ?? '',
                    'price_fen' => (int)round($price * 100), 'stock' => null,
                    'available' => empty($item['disabledStatus']) && empty($item['invalidFlag'])];
            }
        }
        return ['store' => $this->store($row), 'items' => array_values($items)];
    }

    public function movies(string $city, string $film, string $cinema, int $page): array
    {
        $id = $this->cityId($city, true);
        if (!$id) return ['list' => [], 'has_more' => false];
        if ($cinema !== '' && $film !== '') {
            $data = $this->read('movie/schedulelist', ['cinemaId' => $cinema]);
            $shows = [];
            foreach ($data['list'][$film] ?? [] as $day) {
                foreach ($day as $show) {
                    if (empty($show['showId'])) continue;
                    $shows[] = ['id' => $show['showId'], 'name' => $show['hallName'] ?? '', 'time' => $show['showTime'] ?? '',
                        'version' => $show['showVersionType'] ?? $show['planType'] ?? ''];
                }
            }
            return ['list' => $shows, 'has_more' => false];
        }
        if ($film !== '') {
            $data = $this->read('movie/showlist', ['cityId' => $id, 'filmId' => $film, 'page' => max(1,min(1000,$page)), 'limit' => 20]);
            $rows = [];
            foreach ($data['list'] ?? [] as $item) if (!empty($item['cinemaId'])) $rows[] = ['id' => (string)$item['cinemaId'],
                'name' => $item['cinemaName'], 'address' => $item['address'] ?? ''];
            return ['list' => $rows, 'has_more' => !empty($data['hasMore'])];
        }
        $data = $this->read('movie/hotlist', ['cityId' => $id]);
        $rows = [];
        foreach ($data['list'] ?? [] as $item) if (!empty($item['filmId'])) $rows[] = ['id' => (string)$item['filmId'],
            'name' => $item['name'], 'genre' => $item['filmTypes'] ?? '', 'duration' => $item['duration'] ?? '',
            'poster' => str_starts_with($item['pic'] ?? '', 'https://') ? $item['pic'] : ''];
        return ['list' => $rows, 'has_more' => false];
    }
}
