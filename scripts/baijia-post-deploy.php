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
$table = $prefix . 'baijia_cps_order';
$existing = array_column(Db::query("SHOW COLUMNS FROM `{$table}`"), 'Field');
$changes = [];
foreach (['promoter_id' => 'int', 'promoter_commission' => 'decimal(10,2)', 'promoter_credited' => 'tinyint', 'promoter_credited_amount' => 'decimal(10,2)'] as $column => $type) {
    if (!in_array($column, $existing, true)) $changes[] = "ADD COLUMN `{$column}` {$type} NOT NULL DEFAULT 0";
}
$indexes = array_column(Db::query("SHOW INDEX FROM `{$table}`"), 'Key_name');
if (!in_array('idx_promoter', $indexes, true)) $changes[] = 'ADD INDEX idx_promoter (site_id, promoter_id, status)';
if ($changes) Db::execute("ALTER TABLE `{$table}` " . implode(', ', $changes));

echo json_encode([
    'cps_table' => (bool)Db::query("SHOW TABLES LIKE '{$prefix}baijia_cps_order'"),
    'schedules' => Db::name('sys_schedule')->where('addon', 'baijia_tools')->column('status', 'key'),
], JSON_UNESCAPED_UNICODE), PHP_EOL;
