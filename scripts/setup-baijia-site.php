<?php
// Run as the PHP pool user: sudo -u www php setup-baijia-site.php /www/wwwroot/niucloud
// Uses native installer/services. Existing site decoration is never overwritten.
if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
    throw new RuntimeException('Run as the PHP pool user (www), so generated cache files remain accessible');
}
$root = $argv[1] ?? '';
if (!$root || !is_file($root . '/install.lock')) throw new RuntimeException('Expected installed niucloud root');
chdir($root);
require 'vendor/autoload.php';
(new think\App())->initialize();

use think\facade\Db;
use think\facade\Cache;
use app\service\core\addon\CoreAddonInstallService;
use app\service\admin\site\SiteGroupService;
use app\service\admin\site\SiteService;
use app\service\core\diy\CoreDiyConfigService;
use app\service\core\weapp\CoreWeappConfigService;

if (!Db::name('addon')->where('key', 'baijia_tools')->count()) {
    $installer = new CoreAddonInstallService('baijia_tools');
    $check = $installer->installCheck();
    if (!$check['is_pass']) throw new RuntimeException(json_encode($check, JSON_UNESCAPED_UNICODE));
    $installer->install('local');
}

$site = Db::name('site')->where('site_name', '百迦好省')->find();
if ($site) {
    echo json_encode(['site_id' => $site['site_id'], 'existing' => true], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}
$uid = Db::name('sys_user_role')->where('site_id', 0)->where('is_admin', 1)->value('uid');
if (!$uid) throw new RuntimeException('Existing platform administrator not found');
$group = Db::name('site_group')->where('group_name', '百迦好省基础版')->find();
$groupId = $group['group_id'] ?? (new SiteGroupService())->add([
    'group_name' => '百迦好省基础版', 'group_desc' => '生活服务、霸王餐、电影票、美团到店',
    'app' => ['baijia_tools'], 'addon' => []
]);
$siteId = (new SiteService())->add([
    'site_name' => '百迦好省', 'site_domain' => '', 'group_id' => $groupId,
    'expire_time' => '2036-11-09 23:59:59', 'uid' => $uid
]);
request()->siteId($siteId);

// Start on the addon home with the 首页 / 街区 / 我的 bottom bar
// (same as update-baijia-layout.php; each script runs standalone from /tmp).
$link = function ($name, $title, $url) {
    return ['parent' => 'BAIJIA_TOOLS', 'name' => $name, 'title' => $title, 'url' => $url];
};
$home = '/addon/baijia_tools/pages/home/index';
$config = new CoreDiyConfigService();
$config->setStartUpPageConfig($siteId, ['type' => 'DIY_INDEX', 'name' => 'BAIJIA_HOME', 'parent' => 'BAIJIA_TOOLS', 'page' => $home, 'title' => '首页', 'action' => '']);
$bottom = ['backgroundColor' => '#ffffff', 'textColor' => '#8a8c88', 'textHoverColor' => '#202120', 'type' => '1', 'list' => []];
foreach ([['BAIJIA_HOME', '首页', $home, 'home'], ['BAIJIA_MAP', '街区', '/addon/baijia_tools/pages/map/index', 'map'], ['BAIJIA_MINE', '我的', '/addon/baijia_tools/pages/mine/index', 'profile']] as [$name, $title, $url, $icon]) {
    $bottom['list'][] = ['text' => $title, 'link' => $link($name, $title, $url), 'iconPath' => 'addon/baijia_tools/tabs/' . $icon . '.png', 'iconSelectPath' => 'addon/baijia_tools/tabs/' . $icon . '-active.png'];
}
$config->setBottomConfig($siteId, $bottom);
$weapp = new CoreWeappConfigService();
$weapp->setWeappConfig($siteId, array_merge($weapp->getWeappConfig($siteId), ['weapp_name' => '百迦好省', 'app_id' => 'wx63febce51396b8b5']));
Cache::tag('site_cash' . $siteId)->clear();
Cache::tag('sys_menu')->clear();
echo json_encode(['site_id' => $siteId, 'start_page' => $home, 'appid' => 'wx63febce51396b8b5'], JSON_UNESCAPED_UNICODE), PHP_EOL;
