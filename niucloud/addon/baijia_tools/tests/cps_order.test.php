<?php
// Standalone checks for order normalization and rebate rules; no framework required.
require __DIR__ . '/../app/service/core/CpsOrderNormalizer.php';
use addon\baijia_tools\app\service\core\CpsOrderNormalizer as N;
function check($value, $message) { if (!$value) throw new RuntimeException($message); }

check(N::parseSid('s100000m12') === [100000, 12], 'member sid');
check(N::parseSid('s100000g') === [100000, 0], 'guest sid');
check(N::parseSid('test') === [0, 0] && N::parseSid('s1m2x') === [0, 0] && N::parseSid('') === [0, 0], 'foreign sid ignored');

// 聚推客: 1.00 commission × 0.8 net × 50% = 0.40, rounded down
$j = N::jtk(['order_sn' => 'A1', 'sid' => 's100000m7', 'status' => 3, 'jtk_share_fee' => '1.00', 'pay_price' => '20', 'order_title' => '外卖', 'act_name' => '美团外卖', 'pay_time' => '2026-11-10 12:00:00', 'modified_time' => '2026-11-11 08:00:00'], 0.5);
check($j['site_id'] === 100000 && $j['member_id'] === 7 && $j['status'] === N::SETTLED, 'jtk mapped');
check(abs($j['member_commission'] - 0.40) < 1e-9, 'jtk rebate');
check($j['pay_time'] === strtotime('2026-11-10 12:00:00'), 'jtk time');
check(abs(N::memberCommission('jtk', 0.37, 0.5) - 0.14) < 1e-9, 'rounds down, never overpays');
check(N::memberCommission('meituan', 5, 2.0) === 5.0 && N::memberCommission('meituan', 5, -1) === 0.0, 'rate clamped');
check(N::jtk(['status' => 3], 0.5) === null, 'missing order number rejected');

// 美团: status 6 settled; refunds reduce commission; 4/5 invalid with zero rebate
$m = N::meituan(['orderId' => '9', 'sid' => 's100000m7', 'status' => '6', 'profit' => '3.00', 'refundProfit' => '1.00', 'payPrice' => '50', 'productName' => '套餐', 'payTime' => 1762900000, 'updateTime' => 1762990000000], 0.5);
check($m['status'] === N::SETTLED && abs($m['commission'] - 2.0) < 1e-9 && abs($m['member_commission'] - 1.0) < 1e-9, 'meituan settled');
check($m['upstream_update_time'] === 1762990000, 'millisecond timestamps normalized');
$inv = N::meituan(['orderId' => '10', 'sid' => 's100000m7', 'status' => '5', 'profit' => '3'], 0.5);
check($inv['status'] === N::INVALID && $inv['member_commission'] == 0, 'risk-controlled order pays nothing');
$guest = N::meituan(['orderId' => '11', 'sid' => 's100000g', 'status' => '6', 'profit' => '3'], 0.5);
check($guest['member_id'] === 0 && $guest['member_commission'] == 0, 'guest orders earn nothing');

// crediting state machine
check(N::creditAction($m, 0) === 'credit', 'settled → credit once');
check(N::creditAction($m, 1) === 'none', 'no double credit');
check(N::creditAction($inv, 1) === 'reverse', 'invalid after credit → reverse');
check(N::creditAction($inv, 2) === 'none', 'reverse only once');
check(N::creditAction(array_merge($m, ['status' => N::PAID]), 0) === 'none', 'paid but unsettled waits');
check(N::creditAction($guest, 0) === 'none', 'guest never credited');
echo "CPS order checks passed\n";
