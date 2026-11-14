<?php
return [
    [
        'key' => 'baijia_cps_order_sync',
        'name' => '同步聚推客/美团订单并结算返利',
        'desc' => '每 10 分钟按更新时间拉取订单；已结算订单给会员记返利',
        'time' => ['type' => 'min', 'min' => 10],
        'class' => 'addon\baijia_tools\app\job\CpsOrderSync',
        'function' => ''
    ],
];
