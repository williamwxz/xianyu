<?php
namespace addon\baijia_tools\app\service\core;

use app\dict\pay\PayDict;
use app\dict\pay\RefundDict;
use app\service\core\pay\CoreRefundService;
use think\facade\Db;
use think\facade\Log;

class MovieFulfillment
{
    public const TRADE = 'baijia_movie';
    public const LABELS = ['quoted'=>'待确认付款','expired'=>'报价已过期','paying'=>'等待微信支付确认','submitting'=>'正在核对出票结果',
        'pending'=>'出票中','issued'=>'已出票','refund'=>'退款处理中','refunded'=>'已原路退款','review'=>'商户核实中'];

    public function __construct(private ?MovieGateway $gateway = null) {}

    private static function strings($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value,true);
            $value = is_array($decoded) ? $decoded : [$value];
        }
        return array_values(array_filter(is_array($value) ? $value : [],static fn($v)=>is_string($v) && $v !== ''));
    }

    public static function result(array $order, array $params): array
    {
        foreach (['third_orderid','uid'] as $field) {
            if ((string)($order[$field] ?? '') !== $params[$field]) return ['state'=>'review','tickets'=>null];
        }
        if ((string)($order['showid'] ?? '') !== $params['showId'] || empty($order['orderid'])) return ['state'=>'review','tickets'=>null];
        $status = (int)($order['status'] ?? 0);
        if ($status === 10) return ['state'=>'refund','tickets'=>null];
        if (in_array($status,[1,2,3],true)) return ['state'=>'pending','tickets'=>null];
        if (!in_array($status,[4,5],true)) return ['state'=>'review','tickets'=>null];
        $ticket = [];
        foreach (['ticketcode','ticketimage','realseat'] as $key) {
            $ticket[$key] = self::strings($order['ticket'][$key] ?? null) ?: self::strings($order[$key] ?? null);
        }
        $ticket['ticketimage'] = array_values(array_filter($ticket['ticketimage'],static fn($url)=>str_starts_with($url,'https://')));
        if (!$ticket['ticketcode'] && !$ticket['ticketimage']) return ['state'=>'pending','tickets'=>null];
        return ['state'=>'issued','tickets'=>json_encode($ticket,JSON_THROW_ON_ERROR)];
    }

    public function run(): int
    {
        $site = (int)env('HAOJINGKE.SITE_ID',0);
        if (!$site) return 0;
        // Only a settled framework payment belonging to this order can start fulfillment.
        $orders = Db::name('baijia_movie_order')->alias('o')->join('pay p','p.site_id=o.site_id AND p.out_trade_no=BINARY o.out_trade_no AND p.trade_id=o.id AND p.from_main_id=o.member_id AND p.main_id=o.member_id')
            ->where(['o.site_id'=>$site,'p.trade_type'=>self::TRADE,'p.status'=>PayDict::STATUS_FINISH,'p.type'=>PayDict::WECHATPAY,'p.channel'=>'weapp','p.main_type'=>PayDict::MEMBER])
            ->whereRaw('ROUND(p.money * 100) = o.amount_fen')->whereIn('o.state',['paying','pending','submitting','refund','issued'])
            ->where('o.next_check','<=',time())->order('o.next_check,o.id')->limit(50)->field('o.*')->select()->toArray();
        foreach ($orders as $row) {
            try { $this->advance($row); }
            catch (\Throwable $e) { Log::warning('Movie fulfillment requires reconciliation: ' . $row['sn']); }
        }
        return count($orders);
    }

    private function advance(array $row): void
    {
        $table = fn() => Db::name('baijia_movie_order')->where('id',$row['id']);
        $lease = time()+120;
        if (!$table()->where(['state'=>$row['state'],'next_check'=>$row['next_check']])->update(['next_check'=>$lease])) return;
        $save = fn($values) => $table()->where('next_check',$lease)->update($values + ['next_check'=>time()+30]);
        if ($row['state'] === 'refund') {
            $refunds = new CoreRefundService();
            $no = $row['refund_no'];
            if (!$no) {
                $no = Db::transaction(function () use ($table,$lease,$row,$refunds) {
                    $locked = $table()->where('next_check',$lease)->lock(true)->find();
                    if (!$locked) return '';
                    $no = $refunds->create($row['site_id'],$row['out_trade_no'],$row['amount_fen']/100,'影票未出票',self::TRADE,$row['id']);
                    $table()->update(['refund_no'=>$no]);
                    return $no;
                });
            }
            if (!$no) return;
            $status = $refunds->findByRefundNo($row['site_id'],$no)->status;
            if ($status === RefundDict::WAIT) $refunds->refund($row['site_id'],$no);
            elseif ($status === RefundDict::DEALING) $refunds->check($row['site_id'],['out_trade_no'=>$row['out_trade_no'],'refund_no'=>$no]);
            elseif ($status === RefundDict::SUCCESS) $save(['state'=>'refunded']);
            else $save(['state'=>'review']);
            return;
        }
        $payload = json_decode($row['payload'],true,512,JSON_THROW_ON_ERROR);
        $gateway = $this->gateway ?? new MovieGateway($row['site_id']);
        $params = $payload['params'];
        if ($row['state'] === 'paying') {
            if ($row['expires_at'] <= time() || !MovieGateway::approved($row['sn'])) {
                $save(['state'=>'refund']);
                return;
            }
            // Commit the submission marker before the network call. After a timeout only query this serial.
            if (!$table()->where(['state'=>'paying','next_check'=>$lease])->update(['state'=>'submitting'])) return;
            $data = $gateway->call('ordercreate',$params);
        } else {
            $data = $gateway->call('orderdetail',['third_orderid'=>$row['sn']]);
        }
        $result = self::result($data,$params);
        if ($result['state'] === 'issued') {
            $end = strtotime($payload['show']['time'].' Asia/Shanghai') + 86400;
            $result['next_check'] = time() >= $end ? 4294967295 : time()+600;
        }
        $save($result);
    }
}
