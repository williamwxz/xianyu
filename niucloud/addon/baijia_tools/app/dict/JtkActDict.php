<?php
namespace addon\baijia_tools\app\dict;

/**
 * 聚推客活动映射：前端只认 key，换活动只改这里（服务端），无需重新发布小程序。
 * act_id 来自 https://www.jutuike.com/doc/34 活动列表接口。
 */
class JtkActDict
{
    public const ACTS = [
        // 服务入口
        'bawangcan' => ['act_id' => 138, 'name' => '霸王餐'],
        'movie' => ['act_id' => 76, 'name' => '电影票'],
        'daodian' => ['act_id' => 82, 'name' => '到店团购'],
        'waimai' => ['act_id' => 1, 'name' => '外卖红包'],
        'eleme' => ['act_id' => 3, 'name' => '饿了么红包'],
        'express' => ['act_id' => 122, 'name' => '特价快递'],
        'goods' => ['act_id' => 14, 'name' => '拼多多领券'],
        'taxi' => ['act_id' => 18, 'name' => '打车优惠'],
        'hotel' => ['act_id' => 65, 'name' => '特惠酒店'],
        // 大牌点餐
        'kfc' => ['act_id' => 58, 'name' => '肯德基'],
        'pizzahut' => ['act_id' => 64, 'name' => '必胜客'],
        'starbucks' => ['act_id' => 34, 'name' => '星巴克'],
        'luckin' => ['act_id' => 33, 'name' => '瑞幸咖啡'],
        'cotti' => ['act_id' => 136, 'name' => '库迪咖啡'],
        'nayuki' => ['act_id' => 32, 'name' => '奈雪的茶'],
        'heytea' => ['act_id' => 37, 'name' => '喜茶'],
        'burgerking' => ['act_id' => 46, 'name' => '汉堡王'],
        'pagoda' => ['act_id' => 31, 'name' => '百果园'],
    ];

    public static function get(string $key): ?array
    {
        return self::ACTS[$key] ?? null;
    }
}
