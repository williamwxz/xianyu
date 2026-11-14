<?php
namespace addon\baijia_tools\app\job;

use addon\baijia_tools\app\service\core\CpsOrderService;
use core\base\BaseJob;

class CpsOrderSync extends BaseJob
{
    public function doJob()
    {
        $result = (new CpsOrderService())->sync();
        return '订单同步：' . json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
