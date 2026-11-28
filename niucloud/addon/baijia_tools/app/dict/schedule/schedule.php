<?php
return [
    [
        'key' => 'baijia_movie_order_sync',
        'name' => '影票出票及退款核对',
        'desc' => '核对微信实付、出票与取票码',
        'time' => ['type' => 'min', 'min' => 1],
        'class' => 'addon\baijia_tools\app\job\MovieOrderSync',
        'function' => ''
    ],
    [
        'key' => 'baijia_cps_order_sync',
        'name' => '同步聚推客/美团订单并结算返利',
        'desc' => '每 10 分钟按更新时间拉取订单；已结算订单给会员记返利',
        'time' => ['type' => 'min', 'min' => 10],
        'class' => 'addon\baijia_tools\app\job\CpsOrderSync',
        'function' => ''
    ],
];
