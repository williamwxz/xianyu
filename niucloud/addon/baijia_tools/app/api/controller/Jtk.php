<?php
namespace addon\baijia_tools\app\api\controller;

use addon\baijia_tools\app\service\api\JtkService;
use core\base\BaseApiController;
use think\Response;

class Jtk extends BaseApiController
{
    public function acts(): Response
    {
        return success((new JtkService())->acts());
    }

    public function link(): Response
    {
        $data = $this->request->params([['key', '']]);
        return success((new JtkService())->link((string)$data['key']));
    }
}
