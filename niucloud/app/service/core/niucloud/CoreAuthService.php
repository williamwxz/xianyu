<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的saas管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace app\service\core\niucloud;

use core\util\niucloud\BaseNiucloudClient;
use core\util\niucloud\http\Response;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use think\facade\Cache;

/**
 * 官网授权管理服务层
 */
class CoreAuthService extends BaseNiucloudClient
{

    /**
     * @return array|Response|object|ResponseInterface
     * @throws GuzzleException
     */
    public function getAuthInfo()
    {
        return [
            "data" => [
                "id" => 1000,
                "contact_name" => "",
                "company_name" => "",
                "mobile" => "",
                "auth_code" => "202401***********8888",
                "site_address" => "",
                "product_key" => "saas",
                "address_type" => false
            ],
            "msg" => "操作成功",
            "code" => 1
        ];
    }


}
