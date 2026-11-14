<?php
// Standalone contract checks; no framework or credentials required.
require __DIR__ . '/../app/service/core/MeituanClient.php';
use addon\baijia_tools\app\service\core\MeituanClient;
function check($value, $message) { if (!$value) throw new RuntimeException($message); }
$body = '{"platform":2}';
$h = MeituanClient::headers('/cps_open/common/api/v1/query_coupon', $body, 'test-key', 'test-secret', '1700000000000');
check($h['Content-MD5'] === base64_encode(md5($body, true)), 'body digest');
$expected = base64_encode(hash_hmac('sha256', "POST\n" . $h['Content-MD5'] . "\nS-Ca-App:test-key\nS-Ca-Timestamp:1700000000000\n/cps_open/common/api/v1/query_coupon", 'test-secret', true));
check(hash_equals($expected, $h['S-Ca-Signature']), 'signature');
$changed = MeituanClient::headers('/cps_open/common/api/v1/query_coupon', '{"platform":1}', 'test-key', 'test-secret', '1700000000000');
check($changed['S-Ca-Signature'] !== $h['S-Ca-Signature'], 'body tampering changes signature');
$item = ['couponPackDetail' => ['name'=>'套餐', 'productViewSign'=>'ABC_DEF', 'platform'=>2, 'bizLine'=>1, 'sellPrice'=>'17.50', 'originalPrice'=>'35', 'headUrl'=>'http://p0.meituan.net/test.jpg'], 'availablePoiInfo'=>['availablePoiNum'=>0]];
$p = MeituanClient::product($item);
check($p['price']==='17.50' && $p['original_price']==='35.00', 'prices are yuan, not cents');
check($p['image']==='https://p0.meituan.net/test.jpg', 'HTTPS image');
check($p['stores']===0 && $p['id']==='ABC_DEF', 'zero stores and opaque product id');
$item['couponPackDetail']['saleStatus']=false;
check(MeituanClient::product($item)===null, 'unavailable product hidden');
check(MeituanClient::product([])===null, 'malformed product hidden');
unset($item['couponPackDetail']['saleStatus']);$item['couponPackDetail']['sellPrice']=null;
check(MeituanClient::product($item)===null, 'missing price not displayed as free');
echo "Meituan contract checks passed\n";
