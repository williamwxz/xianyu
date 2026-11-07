<?php

return [
    // 默认规则
    'rules' => [
        'applet' => [
            'ext' => ['zip', 'rar', 'xlsx'],
            'mime' => [
                'application/zip',
                'application/vnd.rar',
                'application/x-zip-compressed',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv'
            ],
            'size' => 20971520000 // 这是文件大小限制，当前设置为
        ],
    ],
];

