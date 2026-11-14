<?php
namespace addon\baijia_tools\app\api\controller;
use addon\baijia_tools\app\service\api\MeituanService;
use core\base\BaseApiController;
use think\Response;
class Meituan extends BaseApiController
{
    public function products(): Response
    {
        return success((new MeituanService())->products($this->request->params([
            ['city_id', ''], ['keyword', ''], ['topic', 3], ['cursor', '']
        ])));
    }
    public function link(): Response
    {
        return success((new MeituanService())->link($this->request->params([
            ['id', ''], ['platform', 2], ['biz_line', '']
        ])));
    }
}
