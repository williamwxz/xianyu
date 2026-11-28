<?php
namespace addon\baijia_tools\app\service\core;

use app\dict\pay\OnlinePayDict;
use app\dict\pay\PayDict;
use app\service\core\pay\CorePayEventService;
use app\service\core\pay\CorePayService;
use core\exception\ApiException;
use Yansongda\Artful\Exception\InvalidResponseException;
use app\dict\pay\RefundDict;
use app\service\core\pay\CoreRefundService;
use think\facade\Db;
use think\facade\Log;

class MovieFulfillment
{
    public const TRADE = 'baijia_movie';
    public const LABELS = ['quoted'=>'待确认付款','expired'=>'报价已过期','payment_check'=>'报价已到期，正在核对付款','paying'=>'等待微信支付确认','submitting'=>'正在核对出票结果',
        'pending'=>'出票中','issued'=>'已出票','refund'=>'退款处理中','refunded'=>'已原路退款','review'=>'商户核实中'];

    public function __construct(private ?MovieGateway $gateway = null, private ?CorePayEventService $wechat = null) {}

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
            ->where(['o.site_id'=>$site,'p.trade_type'=>self::TRADE,'p.type'=>PayDict::WECHATPAY,'p.channel'=>'weapp','p.main_type'=>PayDict::MEMBER])
            ->whereRaw('ROUND(p.money * 100) = o.amount_fen')
            ->where(function ($query) {
                $query->where(function ($paid) {
                    $paid->where('p.status', PayDict::STATUS_FINISH)->whereIn('o.state',['paying','expired','pending','submitting','refund','issued']);
                })->whereOr(function ($unpaid) {
                    $unpaid->where('o.state','paying')->whereIn('p.status',[PayDict::STATUS_ING,PayDict::STATUS_CANCEL]);
                });
            })
            ->where('o.next_check','<=',time())->order('o.next_check,o.id')->limit(50)->field('o.*,p.status AS pay_status')->select()->toArray();
        foreach ($orders as $row) {
            try { $this->advance($row); }
            catch (\Throwable $e) { Log::warning(sprintf('Movie %s (%s) reconciliation failed: %s [%s]', $row['sn'], $row['state'], get_class($e), $e->getCode())); }
        }
        return count($orders);
    }

    private function reconcilePayment(array $row): string
    {
        if ((string)$row['pay_status'] === PayDict::STATUS_CANCEL) return 'expired';
        $site = (int)$row['site_id'];
        $number = $row['out_trade_no'];
        $wechat = $this->wechat ?? (new CorePayEventService())->init($site, 'weapp', PayDict::WECHATPAY);
        try {
            $receipt = $wechat->getOrder($number);
        } catch (InvalidResponseException $e) {
            $code = $e->response instanceof \Yansongda\Supports\Collection ? $e->response->get('code') : null;
            // A failed prepay may have created only the local payment record.
            if ($code !== 'ORDER_NOT_EXIST' || time() < $row['expires_at'] + 120) throw $e;
            $receipt = ['out_trade_no'=>$number, 'status'=>OnlinePayDict::CLOSED];
        }
        if (($receipt['out_trade_no'] ?? '') !== $number) return 'paying';
        $status = $receipt['status'];
        if ($status === OnlinePayDict::SUCCESS) {
            if ((int)$receipt['total_fen'] !== (int)$row['amount_fen'] || empty($receipt['trade_no'])) return 'review';
            (new CorePayService())->paySuccess($site, [
                'out_trade_no'=>$number, 'type'=>PayDict::WECHATPAY,
                'trade_no'=>$receipt['trade_no'], 'mch_id'=>$receipt['mch_id']
            ]);
            return 'paid';
        } elseif ($status === OnlinePayDict::CLOSED) {
            return Db::transaction(function () use ($site, $number) {
                $status = Db::name('pay')->where(['site_id'=>$site, 'out_trade_no'=>$number])->lock(true)->value('status');
                if ((string)$status === PayDict::STATUS_FINISH) return 'paying';
                if ((string)$status !== PayDict::STATUS_CANCEL && !(new CorePayService())->payClose($site, ['out_trade_no'=>$number])) return 'paying';
                return 'expired';
            });
        } elseif ($status === OnlinePayDict::NOTPAY && $row['expires_at'] <= time()) {
            // A payment can win the race with close. Wait for the next authoritative query.
            $wechat->close($number);
        }
        return 'paying';
    }

    private function advance(array $row): void
    {
        $table = fn() => Db::name('baijia_movie_order')->where('id',$row['id']);
        $lease = time()+120;
        if (!$table()->where(['state'=>$row['state'],'next_check'=>$row['next_check']])->update(['next_check'=>$lease])) return;
        $save = fn($values) => $table()->where('next_check',$lease)->update($values + ['next_check'=>time()+30]);
        if ((string)$row['pay_status'] !== PayDict::STATUS_FINISH) {
            $payment = $this->reconcilePayment($row);
            if ($payment !== 'paid') {
                $save(['state'=>$payment]);
                return;
            }
        }
        if ($row['state'] === 'expired') {
            $save(['state'=>'refund']);
            return;
        }
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
            $status = $refunds->findByRefundNo($row['site_id'],$no)->status;
            $state = match ($status) {
                RefundDict::WAIT, RefundDict::DEALING => 'refund',
                RefundDict::SUCCESS => 'refunded',
                default => 'review',
            };
            $save(['state'=>$state]);
            return;
        }
        $payload = json_decode($row['payload'],true,512,JSON_THROW_ON_ERROR);
        $gateway = $this->gateway ?? new MovieGateway($row['site_id']);
        $params = $payload['params'];
        if ($row['state'] === 'issued') {
            $time = trim((string)($payload['show']['time'] ?? ''));
            $showAt = $time === '' ? false : strtotime($time . ' Asia/Shanghai');
            if (time() >= ($showAt === false ? (int)$row['expires_at'] : $showAt) + 86400) {
                $save(['next_check'=>4294967295]);
                return;
            }
        }
        if ($row['state'] === 'paying') {
            if ($row['expires_at'] <= time() || !MovieGateway::approved($row['sn'])) {
                $save(['state'=>'refund']);
                return;
            }
            // Commit the submission marker before the network call. After a timeout only query this serial.
            if (!$table()->where(['state'=>'paying','next_check'=>$lease])->update(['state'=>'submitting'])) return;
            $row['state'] = 'submitting';
            // Even a successful create may only acknowledge receipt. The detail endpoint owns ticket state.
            $method = 'ordercreate';
        } else {
            $method = 'orderdetail';
        }
        try {
            if ($method === 'ordercreate') {
                $gateway->call($method, $params);
                $save(['state'=>'submitting']);
                return;
            }
            $data = $gateway->call($method, ['third_orderid'=>$row['sn']]);
        } catch (\Throwable $e) {
            // Gateway ApiException messages are sanitized; never log signed URLs or raw SDK responses.
            $reason = $e instanceof ApiException ? $e->getMessage() : get_class($e);
            Log::warning(sprintf('Movie %s %s failed [%s]: %s', $row['sn'], $method, $e->getCode(), $reason));
            $deadline = $row['state'] === 'pending' ? max((int)$row['expires_at'], (int)($payload['show']['until'] ?? 0)) : (int)$row['expires_at'];
            if ($row['state'] !== 'issued' && time() >= $deadline + 900) $save(['state'=>'review']);
            return;
        }
        $result = self::result($data,$params);
        if ($row['state'] === 'issued' && $result['state'] !== 'refund') {
            // A replacement must carry at least the credentials already delivered in each format.
            $old = json_decode($row['tickets'],true) ?: [];
            $new = json_decode($result['tickets'] ?? 'null',true) ?: [];
            foreach (['ticketcode','ticketimage','realseat'] as $field) {
                if (count($new[$field] ?? []) < count($old[$field] ?? [])) {
                    $result = ['state'=>'issued'];
                    break;
                }
            }
        }
        if ($result['state'] === 'issued') $result['next_check'] = time()+600;
        $save($result);
    }
}
