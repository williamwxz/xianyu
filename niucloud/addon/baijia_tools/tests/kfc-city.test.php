<?php
require __DIR__ . '/../app/service/core/KfcCity.php';
use addon\baijia_tools\app\service\core\KfcCity;

$cities = new KfcCity();
$jiangmen = $cities->name('TO6ILZ7MPJMJN3S7W2SXMIFQQY');
if ($jiangmen !== '江门市' || $cities->name('江门') !== $jiangmen) throw new RuntimeException('City selection mismatch');
foreach ([
    ['济南市天桥区无影山北路', false],
    ['山东省济南市天桥区无影山北路', false],
    ['广东省广州市天河区体育东路', false],
    ['江门蓬江区荷塘镇民丰路35号', true],
    ['广东省台山市台城陈宜禧路南97号', true],
    ['鹤山市沙坪鹤山大道东南侧', true],
    ['雅瑶镇凤亭路528号', true],
    ['济南路商业广场一层', true],
    ['香港路商业广场一层', true],
] as [$address,$expected]) {
    if ($cities->matches(['cityId'=>91,'city'=>'江门','address'=>$address],$jiangmen,91) !== $expected) throw new RuntimeException($address);
}
if ($cities->matches(['cityId'=>2],$jiangmen,91) || $cities->matches(['cityId'=>91,'city'=>'济南'],$jiangmen,91)) throw new RuntimeException('City mismatch');
echo "PASS Jiangmen city identity, foreign addresses, local county-level cities and street names\n";
