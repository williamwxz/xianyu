<?php
namespace addon\baijia_tools\app\api\controller;

use addon\baijia_tools\app\service\api\MovieOrderService;
use core\base\BaseApiController;
use think\Response;

class MovieOrder extends BaseApiController
{
    public function seats(): Response
    {
        return success((new MovieOrderService())->seats($this->request->params([['cinema',''],['film',''],['show','']])));
    }

    public function checkout(): Response
    {
        return success((new MovieOrderService())->checkout($this->request->params([
            ['cinema',''],['film',''],['show',''],['phone',''],['seats',[]],['request_key',''],['amount_fen',0]
        ])));
    }

    public function pay(): Response
    {
        return success((new MovieOrderService())->pay($this->request->params([['sn','']])['sn']));
    }

    public function detail(): Response
    {
        return success((new MovieOrderService())->detail($this->request->params([['sn','']])['sn']));
    }

    public function orders(): Response
    {
        return success((new MovieOrderService())->memberOrders((int)$this->request->params([['page',1]])['page']));
    }
}
