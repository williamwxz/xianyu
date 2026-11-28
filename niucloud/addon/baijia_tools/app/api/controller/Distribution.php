<?php
namespace addon\baijia_tools\app\api\controller;

use addon\baijia_tools\app\service\api\DistributionService;
use core\base\BaseApiController;
use think\Response;

class Distribution extends BaseApiController
{
    public function summary(): Response
    {
        return success((new DistributionService())->summary());
    }

    public function fans(): Response
    {
        return success((new DistributionService())->fans($this->request->params([['page', 1]])));
    }

    public function orders(): Response
    {
        return success((new DistributionService())->orders($this->request->params([['page', 1], ['tab', '']])));
    }
}
