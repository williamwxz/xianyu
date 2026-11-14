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
use app\service\admin\diy\DiyService;
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
    'group_name' => '百迦好省基础版', 'group_desc' => '原生页面装修、优惠计算、省钱指南',
    'app' => ['baijia_tools'], 'addon' => []
]);
$siteId = (new SiteService())->add([
    'site_name' => '百迦好省', 'site_domain' => '', 'group_id' => $groupId,
    'expire_time' => '2036-11-09 23:59:59', 'uid' => $uid
]);
request()->siteId($siteId);

$diy = new DiyService();
$page = $diy->getInfoByName('DIY_INDEX');
if (!$page) throw new RuntimeException('Native DIY homepage was not initialized');
$data = json_decode($page['value'], true);
$data['global']['title'] = '百迦好省';
$data['global']['pageStartBgColor'] = '#f7f8fa';
$data['global']['bottomTabBar']['isShow'] = true;
$data['global']['bottomTabBar']['designNav'] = ['title' => '系统', 'key' => 'app'];
$base = $data['value'][0];
$rich = function ($id, $html, $background = '#ffffff') use ($base) {
    return array_merge($base, [
        'id' => $id, 'path' => 'edit-rich-text', 'componentName' => 'RichText',
        'componentTitle' => '富文本', 'html' => $html,
        'componentStartBgColor' => $background,
        'margin' => ['top' => 10, 'bottom' => 0, 'both' => 12],
        'topRounded' => 12, 'bottomRounded' => 12
    ]);
};
$link = function ($name, $title, $url) {
    return ['parent' => 'BAIJIA_TOOLS', 'name' => $name, 'title' => $title, 'url' => $url];
};
$items = [
    ['折扣计算', 'calculator/index?mode=discount', 'nav_discount.png'],
    ['满减优惠', 'calculator/index?mode=reduction', 'nav_giftcard.png'],
    ['券后到手价', 'calculator/index?mode=coupon', 'nav_coupon.png'],
    ['每满减', 'calculator/index?mode=reduction&repeat=1', 'nav_my_newcomer.png'],
    ['无门槛券', 'calculator/index?mode=coupon&noThreshold=1', 'nav_coupon.png'],
    ['凑单指南', 'article/index?id=threshold', 'nav_giftcard.png'],
    ['叠券指南', 'article/index?id=coupon', 'nav_coupon.png'],
    ['折扣速查', 'article/index?id=discount', 'nav_discount.png'],
    ['计算记录', 'calculator/index', 'nav_my_address.png'],
    ['使用说明', 'about/index', 'nav_my_newcomer.png']
];
$nav = array_merge($base, ['id' => 'baijia-navigation', 'rowCount' => 5, 'imageSize' => 40, 'aroundRadius' => 20, 'list' => [], 'font' => ['size' => 12, 'weight' => 'normal', 'color' => '#303133']]);
foreach ($items as $i => [$title, $route, $image]) {
    $nav['list'][] = ['id' => 'baijia-entry-' . $i, 'title' => $title,
        'imageUrl' => 'static/resource/images/attachment/' . $image,
        'imgWidth' => 100, 'imgHeight' => 100,
        'link' => $link('BAIJIA_ENTRY_' . $i, $title, '/addon/baijia_tools/pages/' . $route),
        'label' => ['control' => false, 'text' => '', 'textColor' => '#ffffff', 'bgColorStart' => '#ff542e', 'bgColorEnd' => '#ff542e']];
}
$data['value'] = [
    $rich('baijia-banner', '<div style="padding:28px 22px;color:#fff;"><p style="font-size:14px;margin:0 0 12px;">百迦好省 · 每一笔都算得清</p><p style="font-size:32px;font-weight:bold;margin:0 0 12px;">把优惠，算明白。</p><p style="font-size:14px;margin:0;">折扣 · 满减 · 券后价</p></div>', '#ff542e'),
    $nav,
    $rich('baijia-guide-heading', '<div style="padding:18px;"><p style="font-size:19px;font-weight:bold;margin:0 0 12px;">先算一算，再决定怎么买</p><p style="font-size:14px;color:#666;line-height:1.8;margin:0;">199 元打 8.5 折，预计到手 169.15 元。<br/>650 元每满 200 减 30，预计到手 560 元。</p></div>'),
    $rich('baijia-note', '<div style="padding:18px;font-size:12px;line-height:1.8;color:#888;">计算结果仅供价格比较，以商家结算规则为准。<br/>当前提供优惠计算与省钱指南，无需登录。</div>')
];
$diy->edit($page['id'], ['title' => '百迦好省', 'page_title' => '百迦好省', 'value' => json_encode($data, JSON_UNESCAPED_UNICODE), 'is_change' => 1]);
$config = new CoreDiyConfigService();
$config->setStartUpPageConfig($siteId, ['type' => 'DIY_INDEX', 'name' => 'INDEX', 'parent' => 'SYSTEM_LINK', 'page' => '/app/pages/index/index', 'title' => '百迦好省', 'action' => 'decorate']);
$bottom = ['backgroundColor' => '#ffffff', 'textColor' => '#666666', 'textHoverColor' => '#ff542e', 'type' => '1', 'list' => []];
foreach ([['首页','/app/pages/index/index','home'],['计算','/addon/baijia_tools/pages/calculator/index','calculator'],['指南','/addon/baijia_tools/pages/guide/index','guide'],['说明','/addon/baijia_tools/pages/about/index','profile']] as $i => [$title,$url,$icon]) {
    $bottom['list'][] = ['text' => $title, 'link' => $link('BAIJIA_TAB_' . $i,$title,$url), 'iconPath' => 'addon/baijia_tools/tabs/' . $icon . '.png', 'iconSelectPath' => 'addon/baijia_tools/tabs/' . $icon . '-active.png'];
}
$config->setBottomConfig($siteId, $bottom);
$weapp = new CoreWeappConfigService();
$weapp->setWeappConfig($siteId, array_merge($weapp->getWeappConfig($siteId), ['weapp_name' => '百迦好省', 'app_id' => 'wx63febce51396b8b5']));
Cache::tag('site_cash' . $siteId)->clear();
Cache::tag('sys_menu')->clear();
echo json_encode(['site_id' => $siteId, 'homepage_id' => $page['id'], 'appid' => 'wx63febce51396b8b5'], JSON_UNESCAPED_UNICODE), PHP_EOL;
