<?php
// +----------------------------------------------------------------------
// | Author: TK
// +----------------------------------------------------------------------

namespace addon\tk_upgrade\app\adminapi\controller;

use addon\tk_upgrade\app\service\core\UpgradeService;
use core\base\BaseAdminController;

class Upgrade extends BaseAdminController
{
    public function checkAuth()
    {
        return success((new UpgradeService())->checkAuth());
    }
    public function getAddonList()
    {
        return success((new UpgradeService())->getAddonList());
    }
    public function installAddon($key)
    {
        (new UpgradeService())->installAddon('tk_upgrade');
        (new UpgradeService())->installAddon($key);
        return success('操作成功');
    }
    public  function getVersionList($key){
        return success((new UpgradeService())->getVersionList($key));
    }
    public function installAll()
    {
        return  success((new UpgradeService())->installAll());
    }
    public function updateAll()
    {
        return success((new UpgradeService())->updateAll());
    }
    public function updateFrame()
    {
        return success((new UpgradeService())->updateFrame());
    }
    public function updateAdmin()
    {
        return success((new UpgradeService())->updateAdmin());
    }
}
