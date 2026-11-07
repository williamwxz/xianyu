<?php
// +----------------------------------------------------------------------
// | Author: TK 开发团队出品
// +----------------------------------------------------------------------

namespace addon\tk_upgrade\app\service\admin;

use app\model\diy\Diy;
use app\model\diy_form\DiyForm;
use core\base\BaseAdminService;

/**
 * 首页工具层
 */
class IndexService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Notes:站点层面修复bottom
     * @Interface repairBottom
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: TK
     * @Time: 2025/7/23   14:41
     */
    public function repairBottom()
    {
        $diy_model = new Diy();
        $where = [
            ['value', '<>', ''],
        ];
        $field = 'id,value';
        $list = $diy_model->where($where)
            ->where(['site_id' => $this->site_id])
            ->field($field)->select()->toArray();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $diy_data = json_decode($v['value'], true);

                if (!isset($diy_data['global']['topStatusBar']['control'])) {
                    $diy_data['global']['topStatusBar']['control'] = true;
                }

                if (!isset($diy_data['global']['bottomTabBar'])) {
                    $diy_data['global']['bottomTabBar'] = [
                        'control' => true,
                        'isShow' => true
                    ];
                }

                if (isset($diy_data['global']['bottomTabBarSwitch'])) {
                    unset($diy_data['global']['bottomTabBarSwitch']);
                }
                if (!isset($diy_data['global']['copyright'])) {
                    $diy_data['global']['copyright'] = [
                        'control' => true,
                        'isShow' => false
                    ];
                }
                if (!isset($diy_data['global']['bottomTabBar']['designNav'])) {
                    $diy_data['global']['bottomTabBar']['designNav'] = ['title'=>'','key'=>''];
                }
                if (!isset($diy_data['global']['copyright'])) {
                    $diy_data['global']['copyright'] = [
                        'control' => true,
                        'isShow' => false
                    ];
                }
                if (!isset($diy_data['global']['bottomTabBar']['designNav'])) {
                    $diy_data['global']['bottomTabBar']['designNav'] = ['title'=>'','key'=>''];
                }
                $diy_data = json_encode($diy_data);
                $diy_model->where([['id', '=', $v['id']]])->update(['value' => $diy_data]);
            }
        }
        $this->handleDiyFormData();
        return true;
    }

    public function install()
    {
        /**
         * 对框架1.1.3之前的bottom进行兼容
         */
        $diy_model = new Diy();
        $where = [
            ['value', '<>', ''],
        ];
        $field = 'id,value';
        $list = $diy_model->where($where)
            ->field($field)->select()->toArray();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $diy_data = json_decode($v['value'], true);

                if (!isset($diy_data['global']['topStatusBar']['control'])) {
                    $diy_data['global']['topStatusBar']['control'] = true;
                }

                if (!isset($diy_data['global']['bottomTabBar'])) {
                    $diy_data['global']['bottomTabBar'] = [
                        'control' => true,
                        'isShow' => true
                    ];
                }

                if (isset($diy_data['global']['bottomTabBarSwitch'])) {
                    unset($diy_data['global']['bottomTabBarSwitch']);
                }
                if (!isset($diy_data['global']['bottomTabBarSwitch'])) {
                    $diy_data['global']['bottomTabBarSwitch'] = true;
                }
                if (!isset($diy_data['global']['copyright'])) {
                    $diy_data['global']['copyright'] = [
                        'control' => true,
                        'isShow' => false
                    ];
                }
                if (!isset($diy_data['global']['bottomTabBar']['designNav'])) {
                    $diy_data['global']['bottomTabBar']['designNav'] = ['title'=>'','key'=>''];
                }
                $diy_data = json_encode($diy_data);
                $diy_model->where([['id', '=', $v['id']]])->update(['value' => $diy_data]);
            }
        }
        $this->handleDiyFormData();
        return true;
    }
    private function handleDiyFormData()
    {
        $diy_form_model = new DiyForm();
        $where = [
            ['value', '<>', '']
        ];
        $field = 'form_id,value';
        $list = $diy_form_model->where($where)->field($field)->select()->toArray();

        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $diy_data = $v['value'];
                if (!isset($diy_data['global']['copyright'])) {
                    $diy_data['global']['copyright'] = [
                        'control' => true,
                        'isShow' => false
                    ];
                }
                if (!isset($diy_data['global']['bottomTabBar']['designNav'])) {
                    $diy_data['global']['bottomTabBar']['designNav'] =  ['title'=>'','key'=>''];
                }

                $diy_form_model->where([['form_id', '=', $v['form_id']]])->update(['value' => $diy_data]);
            }
        }

    }
}
