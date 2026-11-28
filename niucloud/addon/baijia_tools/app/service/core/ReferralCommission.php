<?php
namespace addon\baijia_tools\app\service\core;

/** Approved rule: direct inviter earns 5% of platform commission (before self rebate). */
class ReferralCommission
{
    public const SITE_ID = 100000;
    public const RATE = 0.05;

    public static function amount($commission): float
    {
        // Upstream commission is persisted as DECIMAL(10,2). Calculate in cents,
        // rounding DOWN to a cent; neither self rebate nor NET_FACTOR is deducted.
        $cents = max(0, (int)round((float)$commission * 100));
        return intdiv($cents, 20) / 100;
    }

    public static function delta(int $status, int $credited, $creditedAmount, $target): float
    {
        if ($credited === 2) return 0.0; // a reversed order cannot earn again
        if ($status === CpsOrderNormalizer::INVALID) return $credited === 1 ? -(float)$creditedAmount : 0.0;
        if ($status !== CpsOrderNormalizer::SETTLED) return 0.0;
        return round((float)$target - ($credited === 1 ? (float)$creditedAmount : 0), 2);
    }
}
