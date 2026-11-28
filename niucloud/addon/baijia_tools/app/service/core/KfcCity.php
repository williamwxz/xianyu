<?php
namespace addon\baijia_tools\app\service\core;

class KfcCity
{
    private array $names = [];
    private array $cities = [];
    private array $addressCities = [];
    private array $provinces = [];

    public function __construct()
    {
        $cities = json_decode(file_get_contents(dirname(__DIR__, 3) . '/uni-app/lib/meituan-cities.json'), true, 512, JSON_THROW_ON_ERROR);
        foreach ($cities as $city) {
            $name = $city['name'];
            $this->names[$city['id']] = $name;
            $this->names[preg_replace('/市$/u', '', $name)] = $name;
            $this->cities[$name] = $city['province'];
            if (preg_match('/(?:市|州|盟|地区|特别行政区)$/u', $name)) $this->addressCities[] = $name;
            if (preg_match('/(?:省|市|自治区|特别行政区)$/u', $city['province'])) $this->provinces[$city['province']] = true;
        }
    }

    public function name(string $selection): string
    {
        $selection = trim($selection);
        return $this->names[$selection] ?? $selection;
    }

    public function matches(array $store, string $name, int $supplierId): bool
    {
        if (isset($store['cityId']) && (int)$store['cityId'] !== $supplierId) return false;
        $label = $this->name((string)($store['city'] ?? ''));
        if (isset($this->cities[$label]) && $label !== $name) return false;

        // The supplier can attach the requested cityId even to a store with a foreign address.
        // Only match administrative prefixes: a street such as 济南路 is not a city declaration.
        $address = trim((string)($store['address'] ?? ''));
        foreach ($this->provinces as $province => $_) {
            if (!str_starts_with($address, $province)) continue;
            if (isset($this->cities[$name]) && $this->cities[$name] !== $province) return false;
            $address = ltrim(substr($address, strlen($province)));
            break;
        }
        foreach ($this->addressCities as $city) {
            if (str_starts_with($address, $city)) return $city === $name;
        }
        return true;
    }
}
