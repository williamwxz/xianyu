<?php
// Idempotent post-deploy steps for baijia_tools on an already-installed site.
// Run as the PHP pool user: sudo -u www php baijia-post-deploy.php /www/wwwroot/niucloud
$root = $argv[1] ?? '';
if (!$root || !is_file($root . '/install.lock')) throw new RuntimeException('Expected installed niucloud root');
chdir($root);
require 'vendor/autoload.php';
(new think\App())->initialize();

use app\service\core\addon\CoreAddonInstallService;
use app\service\core\schedule\CoreScheduleInstallService;
use think\facade\Db;

// CREATE TABLE IF NOT EXISTS — safe to repeat
CoreAddonInstallService::executeSql($root . '/addon/baijia_tools/sql/install.sql');
// registers schedule keys that do not exist yet
(new CoreScheduleInstallService())->installAddonSchedule('baijia_tools');

$prefix = config('database.connections.mysql.prefix');
echo json_encode([
    'cps_table' => (bool)Db::query("SHOW TABLES LIKE '{$prefix}baijia_cps_order'"),
    'schedules' => Db::name('sys_schedule')->where('addon', 'baijia_tools')->column('status', 'key'),
], JSON_UNESCAPED_UNICODE), PHP_EOL;
