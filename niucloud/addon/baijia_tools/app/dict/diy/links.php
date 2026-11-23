<?php
// These routes are selectable in niucloud's existing decoration/link editor.
return [
    'BAIJIA_TOOLS' => [
        'title' => '百迦好省',
        'type' => 'link',
        'child_list' => [
            ['name' => 'BAIJIA_HOME', 'title' => '首页', 'url' => '/addon/baijia_tools/pages/home/index', 'is_share' => 1],
            ['name' => 'BAIJIA_MAP', 'title' => '生活街区', 'url' => '/addon/baijia_tools/pages/map/index', 'is_share' => 1],
            ['name' => 'BAIJIA_MINE', 'title' => '我的', 'url' => '/addon/baijia_tools/pages/mine/index', 'is_share' => 0]
        ]
    ]
];
