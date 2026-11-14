<?php
namespace addon\baijia_tools\app\api\controller;

use addon\baijia_tools\app\service\api\CpsOrderApiService;
use core\base\BaseApiController;
use think\Response;

class CpsOrder extends BaseApiController
{
    public function orders(): Response
    {
        $data = $this->request->params([['tab', ''], ['page', 1]]);
        return success((new CpsOrderApiService())->orders($data));
    }

    public function summary(): Response
    {
        return success((new CpsOrderApiService())->summary());
    }
}
