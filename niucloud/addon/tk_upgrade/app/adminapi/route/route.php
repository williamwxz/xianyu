<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的多应用管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

use think\facade\Route;

use app\adminapi\middleware\AdminCheckRole;
use app\adminapi\middleware\AdminCheckToken;
use app\adminapi\middleware\AdminLog;

/**
 * 升级助手
 */
Route::group('tk_upgrade', function () {

     /***************************************************** 升级助手接口 ****************************************************/
    Route::post('addonupload', 'addon\tk_upgrade\app\adminapi\controller\Addon@addonUpload');
    Route::get('checkauthinfo', 'addon\tk_upgrade\app\adminapi\controller\Addon@checkAuthInfo');
    //检查授权
    Route::get('checkauth', 'addon\tk_upgrade\app\adminapi\controller\Upgrade@checkAuth');
    //获取插件列表
    Route::get('getaddonlist', 'addon\tk_upgrade\app\adminapi\controller\Upgrade@getAddonList');
    Route::post('installaddon/:key', 'addon\tk_upgrade\app\adminapi\controller\Upgrade@installAddon');
    Route::get('getversionlist/:key', 'addon\tk_upgrade\app\adminapi\controller\Upgrade@getVersionList');
    //安装所有插件
    Route::post('installall', 'addon\tk_upgrade\app\adminapi\controller\Upgrade@installAll');
    //更新所有插件
    Route::post('updateall', 'addon\tk_upgrade\app\adminapi\controller\Upgrade@updateAll');
    //更新前端
    Route::post('updateadmin', 'addon\tk_upgrade\app\adminapi\controller\Upgrade@updateAdmin');
    Route::get('repairbottom', 'addon\tk_upgrade\app\adminapi\controller\Index@repairBottom');
    Route::get('repairbottom/admin', 'addon\tk_upgrade\app\adminapi\controller\Index@install');

})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);