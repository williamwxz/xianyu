<?php
// +----------------------------------------------------------------------
// | Author: TK
// +----------------------------------------------------------------------

namespace addon\tk_upgrade\app\adminapi\controller;

use addon\tk_upgrade\app\service\admin\AddonService;
use app\dict\sys\ConfigKeyDict;
use app\service\core\niucloud\CoreModuleService;
use app\service\core\sys\CoreConfigService;
use core\base\BaseAdminController;

class Addon extends BaseAdminController
{

    /**
     * @Notes:插件上传
     * @Interface addonUpload
     * @return \think\Response
     * @author: TK
     * @Time: 2024/9/10   下午12:38
     */
   public function addonUpload()
   {
       $data = $this->request->params([
           ['file_url', ''],
           ['addon_key','']
       ]);
       return success('插件安装/升级成功，请执行前端编译', (new AddonService())->uploadAddon($data));
   }
   public function checkAuthInfo()
   {
       return success((new AddonService())->checkAuthInfo());
   }
}
