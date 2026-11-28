<?php
require __DIR__.'/../app/service/core/MovieGateway.php';
require __DIR__.'/../app/service/core/MovieFulfillment.php';
use addon\baijia_tools\app\service\core\MovieGateway;
use addon\baijia_tools\app\service\core\MovieFulfillment;
function same($actual,$expected) {
    if ($actual !== $expected) throw new RuntimeException('Unexpected result: '.json_encode($actual));
}
$params = ['third_orderid'=>'20250219101901912010001','uid'=>'10008','phone'=>'18951000001','seat'=>'1排1座,1排3座','showId'=>'5F814FF46F54358672D5773CF41B3473','cinemaId'=>'666182168','seatId'=>'32019651#12#17|32019651#12#19','seatNo'=>'1排1座|1排3座','area'=>'10104|10104','acceptChangeSeat'=>'0','model'=>'','notifyurl'=>'',
    'cinemaInfo'=>'["卢米埃IMAX影城（南京龙蟠汇激光店）","4号厅-激光高亮-进口设备-皮质座椅","原版2D","周二7月1日11:00","￥40.90/张","名侦探柯南：独眼的残像","https://gw.alicdn.com/tfscom/i2/O1CN01zsIRTP1N0iKMgVwfg_!!6000000001508-0-alipicbeacon.jpg","秦淮区龙蟠中路419号龙蟠商业中心二、三、四层","南京"]'];
same(MovieGateway::sign($params,'testkey','testsecret'),'e681eb3685933b1394cde9996c14c09e');
$row = ['third_orderid'=>$params['third_orderid'],'uid'=>$params['uid'],'showid'=>$params['showId'],'orderid'=>'fixture','status'=>4,
    'ticketcode'=>'["000123","000456"]','ticketimage'=>'','ticket'=>['realseat'=>['1排1座','1排3座']]];
$issued = MovieFulfillment::result($row,$params);
same($issued['state'],'issued');
same(json_decode($issued['tickets'],true)['ticketcode'],['000123','000456']);
same(MovieFulfillment::result(['uid'=>'another']+$row,$params),['state'=>'review','tickets'=>null]);
same(MovieFulfillment::result(['status'=>10]+$row,$params)['state'],'refund');
same(MovieFulfillment::result(['ticketcode'=>'']+$row,$params)['state'],'pending');
echo "PASS movie signature, ticket ownership, pending, issued, closed and multi-ticket codes\n";
