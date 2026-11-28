<?php
namespace addon\baijia_tools\app\service\core;

use core\exception\ApiException;
use GuzzleHttp\Client;

class MovieGateway
{
    public function __construct(private int $site) {}

    public static function sign(array $params, string $key, string $secret): string
    {
        ksort($params, SORT_STRING);
        $edge = 'apikey=' . $key . '&secret=' . $secret;
        $parts = [$edge];
        foreach ($params as $name => $value) {
            if ($value !== null && $value !== '') $parts[] = $name . '=' . $value;
        }
        $parts[] = $edge;
        return md5(implode('&', $parts));
    }

    public function call(string $method, array $params): array
    {
        $key = (string)env('HAOJINGKE.APIKEY', '');
        $secret = (string)env('HAOJINGKE.SECRET', '');
        if (!$key || !$secret || $this->site <= 0 || $this->site !== (int)env('HAOJINGKE.SITE_ID')) throw new ApiException('本站影票服务暂不可用');
        if ($method === 'ordercreate' && !self::approved($params['third_orderid'] ?? '')) throw new ApiException('该笔影票尚待商户确认');
        if (!in_array($method, ['schedulelist', 'seat'], true)) $params['sign'] = self::sign($params, $key, $secret);
        try {
            $response = (new Client(['timeout'=>10, 'connect_timeout'=>4, 'allow_redirects'=>false]))
                ->post('https://api-gw.haojingke.com/index.php/v2/api/movie/' . $method, ['query'=>['apikey'=>$key] + $params]);
            $body = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (\Throwable $e) {
            throw new ApiException('影票供应商响应中断，请稍后查看订单');
        }
        if ((string)($body['status_code'] ?? '') !== '200') throw new ApiException('影票供应商未确认请求，请刷新场次或联系商户', (int)($body['status_code'] ?? 0));
        return is_array($body['data'] ?? null) ? $body['data'] : [];
    }

    public static function approved(string $sn): bool
    {
        return $sn !== '' && hash_equals((string)env('HAOJINGKE.MOVIE_APPROVED_ORDER', ''), $sn);
    }

    public function seating(string $cinema, string $film, string $showId): array
    {
        $schedule = $this->call('schedulelist', ['cinemaId'=>$cinema]);
        foreach ($schedule['list'][$film] ?? [] as $day) {
            foreach ($day as $show) {
                if ((string)$show['showId'] !== $showId || (string)$show['cinemaId'] !== $cinema) continue;
                $until = strtotime(($show['stopSellTime'] ?? '') . ' Asia/Shanghai');
                if (!$until || $until <= time()) throw new ApiException('该场次已停止售票');
                $map = $this->call('seat', ['showId'=>$showId])['seatData'] ?? [];
                return self::layout($show, $map, $until);
            }
        }
        throw new ApiException('该场次已更新，请重新选择');
    }

    public static function layout(array $show, array $map, int $until): array
    {
        $prices = [];
        foreach (explode(';', $show['scheduleArea'] ?? '') as $part) {
            if (preg_match('/^area:([^,]+),price:(\d+)(?:\.0+)?,serviceFee:0(?:\.0+)?$/D', trim($part), $m)) $prices[$m[1]] = (int)$m[2];
        }
        $seats = [];
        foreach ($map['seats'] ?? [] as $seat) {
            if (empty($seat['seatId']) || empty($seat['seatNo'])) continue;
            $area = (string)(($seat['area'] ?? '') ?: '0');
            $price = empty($show['scheduleArea']) ? (int)$show['netPrice'] : ($prices[$area] ?? 0);
            $seats[] = ['id'=>(string)$seat['seatId'], 'label'=>(string)$seat['seatNo'], 'row'=>(int)$seat['row'],
                'column'=>(int)$seat['column'], 'area'=>$area, 'love'=>(int)($seat['lovestatus'] ?? 0),
                'available'=>(string)$seat['status'] === '1' && $price > 0, 'fen'=>$price];
        }
        return ['seats'=>$seats, 'limit'=>max(1,(int)(($map['restrictions'] ?? 0) ?: 4)), 'until'=>$until,
            'film'=>$show['filmName'], 'cinema'=>$show['cinemaName'], 'hall'=>$show['hallName'], 'time'=>$show['showTime'],
            'version'=>$show['showVersionType'] ?? '', 'market_fen'=>(int)$show['netPrice']];
    }

    public function quote(string $cinema, string $film, string $show, array $ids): array
    {
        $map = $this->seating($cinema, $film, $show);
        if (!$ids || count($ids) > $map['limit'] || count($ids) !== count(array_unique($ids, SORT_STRING))) throw new ApiException('请选择有效数量的座位');
        $all = array_column($map['seats'], null, 'id');
        $selected = [];
        foreach ($ids as $id) {
            if (!isset($all[$id]) || !$all[$id]['available']) throw new ApiException('座位已售出或价格已更新，请重新选座');
            $selected[] = $all[$id];
        }
        $positions = [];
        foreach ($selected as $s) $positions[$s['row'].':'.$s['column']] = $s['love'];
        foreach ($selected as $s) {
            if (!$s['love']) continue;
            $other = $s['column'] + ($s['love'] === 1 ? 1 : -1);
            if (($positions[$s['row'].':'.$other] ?? 0) !== (3 - $s['love'])) throw new ApiException('情侣座请成对选择');
        }
        $params = ['cinemaId'=>$cinema, 'showId'=>$show, 'seatId'=>implode('|',$ids),
            'seatNo'=>implode('|',array_column($selected,'label')), 'area'=>implode('|',array_column($selected,'area')),
            'seat'=>implode(',',array_map(static fn($s) => $s['label'] . ($s['love'] ? '(情侣座)' : ''), $selected))];
        $this->call('ordercheck', $params + ['netPrice'=>$map['market_fen']]);
        unset($map['seats']);
        return ['show'=>$map, 'params'=>$params, 'fen'=>array_sum(array_column($selected,'fen'))];
    }
}
