<?php
namespace addon\baijia_tools\app\service\core;

use app\service\core\member\CoreMemberAccountService;
use GuzzleHttp\Client;
use RuntimeException;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;

/**
 * Pulls 聚推客 / 美团 orders by update time, upserts them, and credits member rebates once settled.
 * Idempotent: (source, order_sn) is unique and crediting flips `credited` with a guarded update.
 */
class CpsOrderService
{
    private const TABLE = 'baijia_cps_order';
    private const OVERLAP = 900;        // re-read 15 min each run so late updates are not missed
    private const FIRST_LOOKBACK = 86400 * 3;
    private const MAX_CATCHUP = 86400 * 2; // per run; the next run continues

    public function rate(): float
    {
        return (float)env('CPS.MEMBER_RATE', 0.5);
    }

    /** @return array per-source counts */
    public function sync(?int $now = null): array
    {
        $now = $now ?? time();
        $result = [];
        foreach (['jtk', 'meituan'] as $source) {
            $key = 'baijia_cps_synced_' . $source;
            $last = (int)Cache::get($key, 0) ?: $now - self::FIRST_LOOKBACK;
            $start = $last - self::OVERLAP;
            $end = min($now, $start + self::MAX_CATCHUP);
            try {
                $count = 0;
                // both upstreams cap the window; 聚推客 at 1 hour
                for ($from = $start; $from < $end; $from += 3600) {
                    $to = min($end, $from + 3600);
                    foreach ($source === 'jtk' ? $this->fetchJtk($from, $to) : $this->fetchMeituan($from, $to) as $order) {
                        $row = $source === 'jtk' ? CpsOrderNormalizer::jtk($order, $this->rate()) : CpsOrderNormalizer::meituan($order, $this->rate());
                        if ($row) { $this->save($row); $count++; }
                    }
                }
                Cache::set($key, $end);
                $result[$source] = $count;
            } catch (\Throwable $e) {
                // keep the cursor so the window is retried next run
                Log::write('baijia cps sync ' . $source . ' failed: ' . $e->getMessage());
                $result[$source] = 'error';
            }
        }
        return $result;
    }

    public function save(array $row): void
    {
        $now = time();
        Db::startTrans();
        try {
            $existing = Db::name(self::TABLE)->where(['source' => $row['source'], 'order_sn' => $row['order_sn']])->lock(true)->find();
            if ($existing) {
                // a rebate that was already paid out is never recalculated
                if ((int)$existing['credited'] !== 0) $row['member_commission'] = $existing['member_commission'];
                Db::name(self::TABLE)->where('id', $existing['id'])->update(array_merge($row, ['update_time' => $now]));
                $id = (int)$existing['id'];
                $credited = (int)$existing['credited'];
            } else {
                $id = (int)Db::name(self::TABLE)->insertGetId(array_merge($row, ['credited' => 0, 'create_time' => $now, 'update_time' => $now]));
                $credited = 0;
            }
            $action = CpsOrderNormalizer::creditAction($row, $credited);
            if ($action === 'credit') $this->credit($id, $row);
            if ($action === 'reverse') $this->reverse($id, $row);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            Log::write('baijia cps order ' . $row['source'] . ':' . $row['order_sn'] . ' not saved: ' . $e->getMessage());
        }
    }

    private function credit(int $id, array $row): void
    {
        $changed = Db::name(self::TABLE)->where(['id' => $id, 'credited' => 0])->update(['credited' => 1, 'credited_amount' => $row['member_commission']]);
        if ($changed !== 1) return;
        (new CoreMemberAccountService())->addLog($row['site_id'], $row['member_id'], 'commission', $row['member_commission'], 'cps_rebate',
            '购物返利：' . mb_substr($row['title'] ?: $row['act_name'], 0, 40), $id);
    }

    private function reverse(int $id, array $row): void
    {
        $amount = (float)Db::name(self::TABLE)->where('id', $id)->value('credited_amount');
        $changed = Db::name(self::TABLE)->where(['id' => $id, 'credited' => 1])->update(['credited' => 2]);
        if ($changed !== 1 || $amount <= 0) return;
        // throws ACCOUNT_INSUFFICIENT if already withdrawn; the order is then kept as credited and logged for manual review
        (new CoreMemberAccountService())->addLog($row['site_id'], $row['member_id'], 'commission', -$amount, 'cps_rebate_refund',
            '订单失效，扣回返利：' . mb_substr($row['title'] ?: $row['act_name'], 0, 40), $id);
    }

    private function fetchJtk(int $from, int $to): array
    {
        $apikey = (string)env('JUTUIKE.APIKEY', '');
        if ($apikey === '') return [];
        $all = [];
        for ($page = 1; $page <= 50; $page++) {
            $res = (new Client(['timeout' => 15]))->get('https://api.jutuike.com/union/orders', ['query' => [
                'apikey' => $apikey, 'start_time' => date('Y-m-d H:i:s', $from), 'end_time' => date('Y-m-d H:i:s', $to),
                'query_type' => 2, 'page' => $page, 'pageSize' => 100,
            ]]);
            $body = json_decode((string)$res->getBody(), true);
            if (!is_array($body) || (int)($body['code'] ?? 0) !== 1) {
                $msg = (string)($body['msg'] ?? '');
                if (str_contains($msg, '无数据') || str_contains($msg, '暂无') || str_contains($msg, '没有')) return $all; // empty window, e.g. 【对应条件内无数据】
                throw new RuntimeException('jtk orders: ' . mb_substr($msg, 0, 60));
            }
            $data = $body['data'] ?? [];
            foreach ($data['data'] ?? [] as $o) $all[] = $o;
            if ($page >= (int)($data['last_page'] ?? 1)) break;
        }
        return $all;
    }

    private function fetchMeituan(int $from, int $to): array
    {
        if ((string)env('MEITUAN.APPKEY', '') === '') return [];
        $client = new MeituanClient();
        $all = [];
        foreach ([1, 2] as $platform) {
            $scrollId = '';
            for ($i = 0; $i < 100; $i++) {
                $params = ['platform' => $platform, 'queryTimeType' => 2, 'startTime' => $from, 'endTime' => $to, 'searchType' => 2, 'limit' => 100, 'page' => 1];
                if ($scrollId !== '') $params['scrollId'] = $scrollId;
                $data = $client->call('query_order', $params)['data'] ?? [];
                $list = $data['dataList'] ?? [];
                foreach ($list as $o) $all[] = $o;
                $next = (string)($data['scrollId'] ?? '');
                if (!$list || $next === '' || $next === $scrollId) break;
                $scrollId = $next;
            }
        }
        return $all;
    }
}
