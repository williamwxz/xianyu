<?php
// These routes are selectable in niucloud's existing decoration/link editor.
return [
    'BAIJIA_TOOLS' => [
        'title' => '百迦好省',
        'type' => 'link',
        'child_list' => [
            ['name' => 'BAIJIA_HOME', 'title' => '首页', 'url' => '/addon/baijia_tools/pages/home/index', 'is_share' => 1],
            ['name' => 'BAIJIA_GROUPBUY', 'title' => '同城团购', 'url' => '/addon/baijia_tools/pages/groupbuy/index', 'is_share' => 1],
            ['name' => 'BAIJIA_EXPRESS', 'title' => '聚合快递', 'url' => '/addon/baijia_tools/pages/express/index', 'is_share' => 1],
            ['name' => 'BAIJIA_MINE', 'title' => '我的', 'url' => '/addon/baijia_tools/pages/mine/index', 'is_share' => 0],
            ['name' => 'BAIJIA_CALCULATOR', 'title' => '优惠计算', 'url' => '/addon/baijia_tools/pages/calculator/index', 'is_share' => 1],
            ['name' => 'BAIJIA_GUIDE', 'title' => '省钱指南', 'url' => '/addon/baijia_tools/pages/guide/index', 'is_share' => 1],
            ['name' => 'BAIJIA_ABOUT', 'title' => '使用说明与隐私', 'url' => '/addon/baijia_tools/pages/about/index', 'is_share' => 1]
        ]
    ]
];
