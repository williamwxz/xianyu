<?php
namespace addon\baijia_tools\app\service\core;

/**
 * Pure mapping of upstream orders to our unified row. No framework, so it is unit-tested directly.
 * Unified status: 0 未付款 1 已付款 2 待结算 3 已结算 4 无效
 */
class CpsOrderNormalizer
{
    public const UNPAID = 0, PAID = 1, PENDING = 2, SETTLED = 3, INVALID = 4;
    /** Share of the upstream commission we actually keep (聚推客 deducts 10%, some activities 20%). */
    public const NET_FACTOR = ['jtk' => 0.8, 'meituan' => 1.0];

    /** sid format written by our link services: s{site}m{member} or s{site}g */
    public static function parseSid(string $sid): array
    {
        if (preg_match('/^s(\d{1,9})m(\d{1,9})$/', $sid, $m)) return [(int)$m[1], (int)$m[2]];
        if (preg_match('/^s(\d{1,9})g$/', $sid, $m)) return [(int)$m[1], 0];
        return [0, 0];
    }

    public static function memberCommission(string $source, float $commission, float $rate): float
    {
        $rate = max(0.0, min(1.0, $rate));
        $net = $commission * (self::NET_FACTOR[$source] ?? 0.0);
        return max(0.0, floor($net * $rate * 100 + 1e-6) / 100); // round down: never pay out more than earned
    }

    public static function jtk(array $o, float $rate): ?array
    {
        $sn = trim((string)($o['order_sn'] ?? ''));
        if ($sn === '') return null;
        $status = (int)($o['status'] ?? 0);
        if ($status < 0 || $status > 4) $status = self::UNPAID;
        $commission = max(0.0, (float)($o['jtk_share_fee'] ?? 0));
        return self::row('jtk', $sn, (string)($o['sid'] ?? ''), (string)($o['order_title'] ?? ''), (string)($o['act_name'] ?? ''),
            (float)($o['pay_price'] ?? 0), $commission, $status, self::time($o['pay_time'] ?? null), self::time($o['modified_time'] ?? null), $rate);
    }

    public static function meituan(array $o, float $rate): ?array
    {
        $sn = trim((string)($o['orderId'] ?? ''));
        if ($sn === '') return null;
        $map = [2 => self::PAID, 3 => self::PENDING, 4 => self::INVALID, 5 => self::INVALID, 6 => self::SETTLED];
        $status = $map[(int)($o['status'] ?? 0)] ?? self::PAID;
        $commission = max(0.0, (float)($o['profit'] ?? 0) - (float)($o['refundProfit'] ?? 0));
        if ($status === self::INVALID) $commission = 0.0;
        return self::row('meituan', $sn, (string)($o['sid'] ?? ''), (string)($o['productName'] ?? ''), '美团',
            (float)($o['payPrice'] ?? 0), $commission, $status, self::time($o['payTime'] ?? null), self::time($o['updateTime'] ?? null), $rate);
    }

    private static function row(string $source, string $sn, string $sid, string $title, string $act, float $pay, float $commission, int $status, int $payTime, int $updateTime, float $rate): array
    {
        [$site, $member] = self::parseSid($sid);
        return [
            'source' => $source, 'order_sn' => mb_substr($sn, 0, 64), 'sid' => mb_substr($sid, 0, 64),
            'site_id' => $site, 'member_id' => $member,
            'title' => mb_substr($title, 0, 255), 'act_name' => mb_substr($act, 0, 100),
            'pay_amount' => round(max(0.0, $pay), 2), 'commission' => round($commission, 2),
            'member_commission' => $member > 0 && $status !== self::INVALID ? self::memberCommission($source, $commission, $rate) : 0.0,
            'status' => $status, 'pay_time' => $payTime, 'upstream_update_time' => $updateTime,
        ];
    }

    private static function time($v): int
    {
        if ($v === null || $v === '') return 0;
        if (is_numeric($v)) { $n = (int)$v; return $n > 9999999999 ? intdiv($n, 1000) : $n; }
        $t = strtotime((string)$v);
        return $t === false ? 0 : $t;
    }

    /** What to do with the member account after an upsert. */
    public static function creditAction(array $row, int $credited): string
    {
        if ($credited === 0 && $row['status'] === self::SETTLED && $row['member_id'] > 0 && $row['member_commission'] > 0) return 'credit';
        if ($credited === 1 && $row['status'] === self::INVALID) return 'reverse';
        return 'none';
    }
}
