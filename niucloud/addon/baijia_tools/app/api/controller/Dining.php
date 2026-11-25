<?php
namespace addon\baijia_tools\app\api\controller;

use addon\baijia_tools\app\service\api\DiningService;
use core\base\BaseApiController;
use think\Response;

class Dining extends BaseApiController
{
    public function catalog(): Response
    {
        $args = $this->request->params([['brand', ''], ['city', ''], ['store', '']]);
        return success((new DiningService())->catalog((string)$args['brand'], (string)$args['city'], (string)$args['store']));
    }
}
