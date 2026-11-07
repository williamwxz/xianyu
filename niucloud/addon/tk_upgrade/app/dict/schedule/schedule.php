<?php

return [
    [
        'key' => 'tk_upgrade_clear_schedule_log',
        'name' => '清除定时任务日志',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 30
        ],
        'class' => 'addon\tk_upgrade\app\job\ClearScheduleLog',
        'function' => ''
    ],
];
