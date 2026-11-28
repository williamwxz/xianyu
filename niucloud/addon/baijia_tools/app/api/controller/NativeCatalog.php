<?php
namespace addon\baijia_tools\app\api\controller;

use addon\baijia_tools\app\service\api\NativeCatalogService;
use core\base\BaseApiController;
use think\Response;

class NativeCatalog extends BaseApiController
{
    public function kfc(): Response
    {
        $args = $this->request->params([['city', ''], ['store', '']]);
        return success((new NativeCatalogService())->kfc((string)$args['city'], (string)$args['store']));
    }

    public function movies(): Response
    {
        $args = $this->request->params([['city', ''], ['film', ''], ['cinema', ''], ['page', 1]]);
        return success((new NativeCatalogService())->movies((string)$args['city'], (string)$args['film'], (string)$args['cinema'], (int)$args['page']));
    }
}
