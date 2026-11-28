<?php
namespace addon\baijia_tools\app\service\api;

use addon\baijia_tools\app\service\core\MovieGateway;
use addon\baijia_tools\app\service\core\MovieFulfillment;
use app\dict\pay\PayDict;
use app\model\pay\Pay;
use app\service\core\pay\CorePayEventService;
use app\service\core\pay\CorePayService;
use core\exception\ApiException;
use think\facade\Db;

class MovieOrderService extends MayiService
{
    private function mine()
    {
        return Db::name('baijia_movie_order')->where(['site_id'=>$this->site_id, 'member_id'=>$this->member_id]);
    }

    public function seats(array $args): array
    {
        return (new MovieGateway($this->site_id))->seating($args['cinema'], $args['film'], $args['show']);
    }

    public function checkout(array $args): array
    {
        if (!preg_match('/^1\d{10}$/D', $args['phone'])) throw new ApiException('请填写有效的取票手机号');
        if (!preg_match('/^[A-Za-z0-9_-]{16,48}$/D', $args['request_key'])) throw new ApiException('请重新确认订单');
        $key = $args['request_key'];
        $existing = $this->mine()->where('request_key', $key)->find();
        if ($existing) return $this->view($existing);
        $ids = $args['seats'];
        if (!is_array($ids) || count($ids) > 100 || count(array_filter($ids,'is_string')) !== count($ids)) throw new ApiException('座位信息有误');
        $quote = (new MovieGateway($this->site_id))->quote($args['cinema'], $args['film'], $args['show'], $ids);
        if ((int)$args['amount_fen'] !== $quote['fen']) throw new ApiException('票价已更新，请返回刷新座位图');
        $sn = 'BQ' . bin2hex(random_bytes(12));
        $info = $quote['show'];
        $params = $quote['params'] + ['third_orderid'=>$sn, 'uid'=>$this->uid(), 'phone'=>$args['phone'], 'acceptChangeSeat'=>'0',
            'cinemaInfo'=>json_encode([$info['cinema'],$info['hall'],$info['version'],$info['time'],'',$info['film'],'','',''], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)];
        $row = ['site_id'=>$this->site_id, 'member_id'=>$this->member_id, 'request_key'=>$key, 'sn'=>$sn, 'amount_fen'=>$quote['fen'],
            'expires_at'=>min(time()+900,$info['until']), 'payload'=>json_encode(['film_id'=>$args['film'], 'show'=>$info, 'params'=>$params], JSON_THROW_ON_ERROR)];
        return Db::transaction(function () use ($row, $key) {
            Db::name('member')->where(['site_id'=>$this->site_id,'member_id'=>$this->member_id])->lock(true)->find();
            $existing = $this->mine()->where('request_key', $key)->lock(true)->find();
            if ($existing) return $this->view($existing);
            Db::name('baijia_movie_order')->insert($row);
            return $this->detail($row['sn']);
        });
    }

    public function pay(string $sn): array
    {
        if (!MovieGateway::approved($sn)) throw new ApiException('本笔实付须经商户确认');
        $row = $this->mine()->where('sn',$sn)->find();
        if (!$row || !in_array($row['state'], ['quoted','paying'],true) || $row['expires_at'] <= time()) throw new ApiException('订单已更新，请重新选座');
        $member = Db::name('member')->where(['site_id'=>$this->site_id,'member_id'=>$this->member_id])->find();
        if ($this->channel !== 'weapp' || empty($member['weapp_openid'])) throw new ApiException('请在微信小程序内登录后支付');
        $p = json_decode($row['payload'],true);
        $params = $p['params'];
        $quote = (new MovieGateway($this->site_id))->quote($params['cinemaId'],$p['film_id'],$params['showId'],explode('|',$params['seatId']));
        if ($quote['fen'] !== (int)$row['amount_fen'] || $quote['params'] !== array_intersect_key($params,$quote['params'])) throw new ApiException('座位或价格已更新，请重新选座');
        $money = $quote['fen'] / 100;
        $number = Db::transaction(function () use ($sn, $money) {
            $order = $this->mine()->where('sn',$sn)->lock(true)->find();
            if ($order['expires_at'] <= time() || !in_array($order['state'], ['quoted','paying'],true)) throw new ApiException('订单已更新');
            $number = $order['out_trade_no'];
            if (!$number) {
                $number = (new CorePayService())->create($this->site_id, PayDict::MEMBER, $this->member_id, $money, MovieFulfillment::TRADE, $order['id'], '的确省电影票');
                $this->mine()->where('sn',$sn)->update(['state'=>'paying','out_trade_no'=>$number]);
            }
            $pay = (new Pay())->where(['site_id'=>$this->site_id,'out_trade_no'=>$number])->lock(true)->find();
            if (!$pay || !in_array((string)$pay->status,[PayDict::STATUS_WAIT,PayDict::STATUS_ING],true)) throw new ApiException('支付状态已更新，请查看订单');
            $pay->save(['type'=>PayDict::WECHATPAY,'channel'=>'weapp','status'=>PayDict::STATUS_ING]);
            return $number;
        });
        return (new CorePayEventService())->init($this->site_id,'weapp',PayDict::WECHATPAY)->pay($number,$money,'的确省电影票','','','',$member['weapp_openid']);
    }

    private function view(array $row): array
    {
        $p = json_decode($row['payload'],true);
        $state = $row['state'];
        if ($state === 'quoted' && $row['expires_at'] <= time()) $state = 'expired';
        if ($state === 'paying' && $row['expires_at'] <= time()) $state = 'payment_check';
        return ['id'=>$row['sn'],'title'=>$p['show']['film'],'cinema'=>$p['show']['cinema'],'time'=>$p['show']['time'],
            'seat'=>$p['params']['seat'],'state'=>$state,'status'=>MovieFulfillment::LABELS[$state],
            'amount'=>number_format($row['amount_fen']/100,2,'.',''), 'expires_at'=>$row['expires_at'],
            'can_pay'=>in_array($state,['quoted','paying'],true) && $row['expires_at'] > time() && MovieGateway::approved($row['sn']),
            'tickets'=>$state === 'issued' ? json_decode($row['tickets'],true) : null];
    }

    public function detail(string $sn): array
    {
        $row = $this->mine()->where('sn',$sn)->find();
        if (!$row) throw new ApiException('影票订单不存在');
        return $this->view($row);
    }

    public function memberOrders(int $pageNumber): array
    {
        $rows = $this->mine()->order('id','desc')->page(max(1,$pageNumber),20)->select()->toArray();
        return ['list'=>array_map(fn($r) => $this->view($r),$rows),'has_more'=>count($rows) === 20];
    }
}
