<?php

namespace addon\tk_upgrade\app\service\core;

use app\service\core\addon\CoreAddonService;
use core\base\BaseAdminService;
use core\exception\CommonException;
use think\Exception;
use think\facade\Request;
use app\service\core\niucloud\CoreNiucloudConfigService;

/**
 * 公用服务层
 */
class CloudService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->mainInfo = (new CoreNiucloudConfigService())->getNiucloudConfig();
    }

    /**
     * 获取授权码
     */
    public function getAuthCode()
    {
        $fromPath = root_path() . 'addon' . DIRECTORY_SEPARATOR . 'tk_upgrade' . DIRECTORY_SEPARATOR . 'auth.crt';
        if (file_exists($fromPath)) {
            $content = file_get_contents($fromPath);
            return $content == '100000' ? '' : $content;
        } else {
            file_put_contents($fromPath, '100000');
            return '';
        }
    }

    public function auth()
    {
        $addonInfo = (new CoreAddonService())->getAddonConfig('tk_upgrade');
        $data = [
            'domain' => Request::domain(),
            'ip' => Request::ip(),
            'addon_key' => $addonInfo['key'],
            'version' => $addonInfo['version'],
            'main_code' => $this->mainInfo['auth_code'] == '' ? '100000' : $this->mainInfo['auth_code'],
            'main_key' => $this->mainInfo['auth_secret'] == '' ? '100000' : $this->mainInfo['auth_secret'],
            'is_saas' => '1'
        ];
        $auth_code = $this->getAuthCode();
        if ($auth_code != '') {
            $data['main_code'] = '100000';
            $data['main_key'] = '100000';
            $data['auth_code'] = $auth_code;
        }
        $res = $this->cloudPost('api/tkcloud/auth', $data);
        if (isset($res['data']['action']) && $res['data']['action'] != 'success') {
            throw new CommonException($res['data']['msg']);
        }
        return $res['data']['data'] ?? '';
    }

    public function updateVersion($addon_key)
    {
        $data = [
            'domain' => Request::domain(),
            'ip' => Request::ip(),
            'addon_key' => $addon_key,
            'version' => '',
            'main_code' => $this->mainInfo['auth_code'] == '' ? '100000' : $this->mainInfo['auth_code'],
            'main_key' => $this->mainInfo['auth_secret'] == '' ? '100000' : $this->mainInfo['auth_secret'],
            'is_saas' => '1'
        ];
        $auth_code = $this->getAuthCode();
        if ($auth_code != '') {
            $data['main_code'] = '100000';
            $data['main_key'] = '100000';
            $data['auth_code'] = $auth_code;
        }
        $res = $this->cloudPost('api/tkcloud/updateversion', $data);
        return $res['data'] ?? [];
    }

    public function versionList($addon_key)
    {
        $data = [
            'domain' => Request::domain(),
            'ip' => Request::ip(),
            'addon_key' => $addon_key,
            'version' => '',
            'auth_code' => '100000',
            'main_code' => $this->mainInfo['auth_code'] == '' ? '100000' : $this->mainInfo['auth_code'],
            'main_key' => $this->mainInfo['auth_secret'] == '' ? '100000' : $this->mainInfo['auth_secret'],
            'is_saas' => '1'
        ];
        $auth_code = $this->getAuthCode();
        if ($auth_code != '') {
            $data['main_code'] = '100000';
            $data['main_key'] = '100000';
            $data['auth_code'] = $auth_code;
        }
        $res = $this->cloudPost('api/tkcloud/versioninfo', $data);
        return $res['data']['data'] ?? [];
    }

    public function getAddonList()
    {
        $addonInfo = (new CoreAddonService())->getAddonConfig('tk_upgrade');
        $data = [
            'domain' => Request::domain(),
            'ip' => Request::ip(),
            'addon_key' => $addonInfo['key'],
            'version' => $addonInfo['version'],
            'auth_code' => '100000',
            'main_code' => $this->mainInfo['auth_code'] == '' ? '100000' : $this->mainInfo['auth_code'],
            'main_key' => $this->mainInfo['auth_secret'] == '' ? '100000' : $this->mainInfo['auth_secret'],
            'is_saas' => '1',
            'page'=>1,
            'limit'=>120,
        ];
        $auth_code = $this->getAuthCode();
        if ($auth_code != '') {
            $data['main_code'] = '100000';
            $data['main_key'] = '100000';
            $data['auth_code'] = $auth_code;
        }
        $res = $this->cloudPost('api/tkcloud/addonlist', $data);
        return $res['data']['data'] ?? [];
    }

    public function cloudPost($method, $data = null, $headers = false)
    {
        $baseUrl = base64_decode("aHR0cDovL2Muc290dWkudG9wLw==");
        $baseUrl = 'https://cps.ikysc.cn/';
        $url = $baseUrl . $method;
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            $curl = curl_init();
            //设置header头
            if ($headers !== false) curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_URL, $url);

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时时间为30秒
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); // 设置连接超时时间为10秒

            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    throw new CommonException($err);
                }
                sleep(1); // 等待1秒后重试
            } else {
                return json_decode($result, true);
            }
        }
    }

}