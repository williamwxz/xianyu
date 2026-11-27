<?php
namespace addon\baijia_tools\app\api\controller;

use addon\baijia_tools\app\service\api\MayiService;
use core\base\BaseApiController;
use think\Response;

class Mayi extends BaseApiController
{
    public function launch(): Response
    {
        return success((new MayiService())->launch());
    }

    public function orders(): Response
    {
        $args = $this->request->params([['kind', 'recharge'], ['page', 1]]);
        return success((new MayiService())->orders((string)$args['kind'], (int)$args['page']));
    }
}
