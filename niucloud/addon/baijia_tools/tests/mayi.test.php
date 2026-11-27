<?php
require __DIR__ . '/../app/service/core/MayiClient.php';
use addon\baijia_tools\app\service\core\MayiClient;

function same($actual, $expected): void {
    if ($actual !== $expected) throw new RuntimeException('Unexpected result: ' . json_encode($actual));
}

same(MayiClient::sign(['uid'=>'13915969891', 'goods_id'=>1, 'apikey'=>'unused', 'rechargeno'=>'13915969891', 'orderid'=>''], 'myxqsecret'), 'f8677d7b3c86038436855b8484413c22');
$row = ['uid'=>'member-a', 'orderid'=>'332022051711554077357', 'status'=>3, 'payprice'=>'19.40', 'takeCode'=>'A089', 'storeName'=>'测试店'];
$data = ['list'=>[$row, array_merge($row, ['uid'=>'member-b', 'takeCode'=>'private'])], 'total'=>2];
$page = MayiClient::memberPage($data, 'dining', 'member-a', 1);
same(count($page['list']), 1);
same($page['list'][0]['pickup'], 'A089');
same($page['list'][0]['amount'], '19.40');
same($page['list'][0]['id'], '332022051711554077357');
same($page['has_more'], false);
$page = MayiClient::memberPage(['list'=>[array_merge($row, ['payprice'=>1234])], 'total'=>21], 'recharge', 'member-a', 1);
same($page['list'][0]['amount'], '12.34');
same($page['list'][0]['pickup'], '');
same($page['has_more'], true);
echo "PASS Mayi signing, ownership, pickup, amounts and pagination\n";
