<?php
namespace addon\baijia_tools\app\service\api;

use core\base\BaseApiService;
use think\facade\Db;

/** Single-level referral reporting, using the member's immutable registration pid. */
class DistributionService extends BaseApiService
{
    private function fansQuery()
    {
        return Db::name('member')->where('site_id', $this->site_id)
            ->where('pid', $this->member_id)->where('is_del', 0)->where('delete_time', 0);
    }

    private function ordersQuery()
    {
        return Db::name('baijia_cps_order')->where('site_id', $this->site_id)
            ->where('promoter_id', $this->member_id);
    }

    public function summary(): array
    {
        $orders = $this->ordersQuery()->where('status', 'in', [1, 2, 3]);
        $promotion = $this->ordersQuery();
        return [
            'promotion_rate' => 0.05,
            'promotion_credited' => number_format((float)(clone $promotion)->where('promoter_credited', 1)->sum('promoter_credited_amount'), 2, '.', ''),
            'promotion_pending' => number_format((float)(clone $promotion)->where('status', 'in', [1, 2])->where('promoter_credited', 0)->sum('promoter_commission'), 2, '.', ''),
            'fans' => $this->fansQuery()->count(),
            'paying_fans' => (clone $orders)->group('member_id')->count(),
            'orders' => (clone $orders)->count(),
            'turnover' => number_format((float)(clone $orders)->sum('pay_amount'), 2, '.', ''),
        ];
    }

    public function fans(array $input): array
    {
        $page = max(1, (int)($input['page'] ?? 1));
        $query = $this->fansQuery();
        $total = (clone $query)->count();
        // No phone, openid, address or other private account fields are returned.
        $rows = $query->field('member_id, member_no, create_time')
            ->order('member_id desc')->page($page, 20)->select()->toArray();
        foreach ($rows as &$row) $row['create_time'] = date('Y-m-d', (int)$row['create_time']);
        return ['list' => $rows, 'total' => $total, 'page' => $page, 'has_more' => $page * 20 < $total];
    }

    public function orders(array $input): array
    {
        $page = max(1, (int)($input['page'] ?? 1));
        $query = $this->ordersQuery();
        $tab = (string)($input['tab'] ?? '');
        if ($tab === 'pending') $query->where('status', 'in', [1, 2]);
        if ($tab === 'settled') $query->where('status', 3);
        if ($tab === 'invalid') $query->where('status', 4);
        $total = (clone $query)->count();
        $rows = $query->field('id, source, title, act_name, pay_amount, status, pay_time, commission, promoter_commission, promoter_credited, promoter_credited_amount')
            ->order('pay_time desc, id desc')->page($page, 20)->select()->toArray();
        $states = [0 => '未付款', 1 => '已付款', 2 => '待结算', 3 => '已结算', 4 => '已失效'];
        foreach ($rows as &$row) {
            $row['status_name'] = $states[(int)$row['status']] ?? '';
            $row['source_name'] = $row['source'] === 'meituan' ? '美团' : '聚推客';
            $row['pay_time'] = $row['pay_time'] ? date('Y-m-d H:i', (int)$row['pay_time']) : '';
        }
        return ['list' => $rows, 'total' => $total, 'page' => $page, 'has_more' => $page * 20 < $total];
    }
}
