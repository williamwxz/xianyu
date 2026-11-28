<?php
namespace addon\baijia_tools\app\job;

use addon\baijia_tools\app\service\core\MovieFulfillment;
use core\base\BaseJob;

class MovieOrderSync extends BaseJob
{
    public function doJob()
    {
        return '核对影票订单 ' . (new MovieFulfillment())->run();
    }
}
