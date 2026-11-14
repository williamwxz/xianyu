<?php
namespace addon\baijia_tools\app\service\api;

use core\base\BaseApiService;
use think\facade\Db;

class CpsOrderApiService extends BaseApiService
{
    private const STATUS = [0 => '未付款', 1 => '已付款', 2 => '待结算', 3 => '已结算', 4 => '已失效'];
    private const SOURCE = ['jtk' => '聚推客', 'meituan' => '美团'];

    public function orders(array $input): array
    {
        $where = [['site_id', '=', $this->site_id], ['member_id', '=', $this->member_id]];
        $tab = (string)($input['tab'] ?? '');
        if ($tab === 'pending') $where[] = ['status', 'in', [1, 2]];
        if ($tab === 'settled') $where[] = ['status', '=', 3];
        if ($tab === 'invalid') $where[] = ['status', '=', 4];
        $page = max(1, (int)($input['page'] ?? 1));
        $query = Db::name('baijia_cps_order')->where($where);
        $total = (clone $query)->count();
        $rows = $query->order('pay_time desc, id desc')->page($page, 20)
            ->field('id, source, title, act_name, pay_amount, member_commission, status, credited, pay_time')->select()->toArray();
        foreach ($rows as &$r) {
            $r['status_name'] = self::STATUS[(int)$r['status']] ?? '';
            $r['source_name'] = self::SOURCE[$r['source']] ?? '';
            $r['pay_time'] = $r['pay_time'] ? date('Y-m-d H:i', (int)$r['pay_time']) : '';
        }
        return ['list' => $rows, 'total' => $total, 'page' => $page, 'has_more' => $page * 20 < $total];
    }

    public function summary(): array
    {
        $base = Db::name('baijia_cps_order')->where([['site_id', '=', $this->site_id], ['member_id', '=', $this->member_id]]);
        return [
            'credited' => round((float)(clone $base)->where('credited', 1)->sum('credited_amount'), 2),
            'pending' => round((float)(clone $base)->where('status', 'in', [1, 2])->sum('member_commission'), 2),
            'order_count' => (clone $base)->where('status', 'in', [1, 2, 3])->count(),
            'rate' => (float)env('CPS.MEMBER_RATE', 0.5),
        ];
    }
}
