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

namespace addon\tk_upgrade\app\adminapi\controller;

use addon\tk_upgrade\app\service\admin\IndexService;
use core\base\BaseAdminController;
use think\Response;

class Index extends BaseAdminController
{

    public function repairBottom()
    {
        (new IndexService())->repairBottom();
        return success('操作成功');
    }

    public function install()
    {
        (new IndexService())->install();
        return success('操作成功');
    }
}
