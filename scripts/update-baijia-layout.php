<?php
// Switches the 的确省 (formerly 百迦好省) site to the 首页 / 街区 / 我的 layout.
// Run as the PHP pool user: sudo -u www php update-baijia-layout.php /www/wwwroot/niucloud
// Only touches the start page and bottom navigation; re-running gives the same result.
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
use app\service\core\diy\CoreDiyConfigService;

$siteId = Db::name('site')->whereIn('site_name', ['的确省', '百迦好省'])->value('site_id');
if (!$siteId) throw new RuntimeException('的确省 site not found; run setup-baijia-site.php first');
request()->siteId($siteId);

$link = function ($name, $title, $url) {
    return ['parent' => 'BAIJIA_TOOLS', 'name' => $name, 'title' => $title, 'url' => $url];
};
$home = '/addon/baijia_tools/pages/home/index';
$config = new CoreDiyConfigService();
$config->setStartUpPageConfig($siteId, ['type' => 'DIY_INDEX', 'name' => 'BAIJIA_HOME', 'parent' => 'BAIJIA_TOOLS', 'page' => $home, 'title' => '首页', 'action' => '']);

$tabs = [
    ['BAIJIA_HOME', '首页', $home, 'home'],
    ['BAIJIA_MAP', '街区', '/addon/baijia_tools/pages/map/index', 'map'],
    ['BAIJIA_MINE', '我的', '/addon/baijia_tools/pages/mine/index', 'profile'],
];
$bottom = ['backgroundColor' => '#ffffff', 'textColor' => '#8a8c88', 'textHoverColor' => '#202120', 'type' => '1', 'list' => []];
foreach ($tabs as [$name, $title, $url, $icon]) {
    $bottom['list'][] = ['text' => $title, 'link' => $link($name, $title, $url),
        'iconPath' => 'addon/baijia_tools/tabs/' . $icon . '.png',
        'iconSelectPath' => 'addon/baijia_tools/tabs/' . $icon . '-active.png'];
}
$config->setBottomConfig($siteId, $bottom);
Cache::tag('site_cash' . $siteId)->clear();
echo json_encode(['site_id' => $siteId, 'start_page' => $home, 'tabs' => array_column($tabs, 1)], JSON_UNESCAPED_UNICODE), PHP_EOL;
