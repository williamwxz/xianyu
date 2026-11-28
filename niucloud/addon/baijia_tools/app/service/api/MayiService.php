<?php
namespace addon\baijia_tools\app\service\api;

use addon\baijia_tools\app\service\core\MayiClient;
use core\base\BaseApiService;
use core\exception\ApiException;

class MayiService extends BaseApiService
{
    private function credentials(): array
    {
        $key = (string)env('HAOJINGKE.APIKEY', '');
        $secret = (string)env('HAOJINGKE.SECRET', '');
        if ((int)$this->site_id !== (int)env('HAOJINGKE.SITE_ID', 0) || $key === '' || $secret === '') {
            throw new ApiException('蚂蚁星球服务暂未开通');
        }
        return [$key, $secret];
    }

    protected function uid(): string
    {
        $uidKey = (string)env('HAOJINGKE.UID_KEY', '');
        if (strlen($uidKey) < 32) throw new ApiException('蚂蚁星球会员关联暂未配置');
        return substr(hash_hmac('sha256', $this->site_id . ':' . $this->member_id, $uidKey), 0, 24);
    }

    public function launch(): array
    {
        [$key] = $this->credentials();
        return ['apikey'=>$key, 'uid'=>$this->uid()];
    }

    public function orders(string $kind, int $page): array
    {
        [$key, $secret] = $this->credentials();
        try {
            return MayiClient::orders($kind, max(1, min(100000, $page)), $this->uid(), $key, $secret);
        } catch (\RuntimeException $e) {
            throw new ApiException($e->getMessage());
        }
    }
}
