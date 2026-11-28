<?php
// Run with PHP CLI and an installed niucloud test database. All fixtures use connection-local temporary tables.
use addon\baijia_tools\app\service\core\CpsOrderService;
use addon\baijia_tools\app\service\core\MovieFulfillment;
use addon\baijia_tools\app\service\core\MovieGateway;
use app\service\core\pay\CorePayEventService;
use app\service\core\pay\CorePayService;
use think\facade\Db;
use think\facade\Event;

$root = getenv('NIUCLOUD_TEST_ROOT') ?: dirname(__DIR__, 3);
chdir($root);
require 'vendor/autoload.php';
$app = new think\App();
$app->setRuntimePath(sys_get_temp_dir() . '/baijia-test-' . getmypid() . '/');
$app->initialize();
$prefix = config('database.connections.' . config('database.default'))['prefix'];
foreach (['baijia_movie_order', 'pay', 'baijia_cps_order', 'member', 'member_account_log'] as $table) {
    $ddl = Db::query("SHOW CREATE TABLE `{$prefix}{$table}`")[0]['Create Table'];
    Db::execute(preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $ddl));
}
foreach (['PaySuccess','PayClose','MemberAccount'] as $event) Event::remove($event);
function verify($condition, string $label): void
{
    if (!$condition) throw new RuntimeException($label);
    echo "PASS $label\n";
}

$site = 100000;
$sn = 'fixture-issue';
$number = 'fixture-pay';
$app->env->set('HAOJINGKE.SITE_ID', $site);
$app->env->set('HAOJINGKE.MOVIE_APPROVED_ORDER', $sn);
$params = ['third_orderid'=>$sn,'uid'=>'fixture-member','showId'=>'fixture-show'];
$payload = ['params'=>$params,'show'=>['time'=>'invalid-date','until'=>time()+900]];
$order = fn() => Db::name('baijia_movie_order')->where('sn',$sn);
$pay = fn() => Db::name('pay')->where('out_trade_no',$number);
$id = Db::name('baijia_movie_order')->insertGetId(['site_id'=>$site,'member_id'=>7,'sn'=>$sn,'request_key'=>'fixture',
    'amount_fen'=>4250,'expires_at'=>time()+900,'state'=>'paying','payload'=>json_encode($payload),'out_trade_no'=>$number]);
Db::name('pay')->insert(['site_id'=>$site,'out_trade_no'=>$number,'trade_type'=>MovieFulfillment::TRADE,'trade_id'=>$id,'from_main_id'=>7,
    'main_id'=>7,'main_type'=>'member','status'=>1,'channel'=>'weapp','type'=>'wechatpay','money'=>'42.50','body'=>'fixture']);
$wechat = new class extends CorePayEventService {
    public string $status = 'NOTPAY';
    public int $closes = 0;
    public function getOrder(string $out_trade_no) {
        return ['out_trade_no'=>$out_trade_no,'trade_no'=>'fixture-wx','mch_id'=>'fixture-merchant','total_fen'=>4250,'status'=>$this->status];
    }
    public function close(string $out_trade_no) { ++$this->closes; }
};
$gateway = new class($site) extends MovieGateway {
    public int $creates = 0;
    public ?array $detail = null;
    public function call(string $method, array $params): array {
        if ($method === 'ordercreate') { ++$this->creates; return []; }
        if ($method !== 'orderdetail' || $this->detail === null) throw new RuntimeException('fixture missing upstream order');
        return $this->detail;
    }
};
$job = new MovieFulfillment($gateway,$wechat);
$tick = function () use ($job,$order) { $order()->update(['next_check'=>0]); verify($job->run() === 1,'scheduled order eligible'); };
$tick();
verify(!$gateway->creates && $order()->value('state') === 'paying','unpaid order stays payable');
$wechat->status = 'SUCCESS';
$events = 0;
Event::listen('PaySuccess',function () use (&$events) { ++$events; });
$tick();
verify((int)$pay()->value('status') === 2 && $pay()->value('trade_no') === 'fixture-wx','missed notification recovered');
(new CorePayService())->paySuccess($site,['out_trade_no'=>$number,'type'=>'wechatpay']);
verify($events === 1,'duplicate settlement emits one accounting event');
verify($gateway->creates === 1 && $order()->value('state') === 'submitting','minimal creation acknowledgement stays reconcilable');
$gateway->detail = ['third_orderid'=>$sn,'uid'=>$params['uid'],'orderid'=>'fixture-ticket','showid'=>$params['showId'],'status'=>4,'ticketcode'=>['000123']];
$tick();
$tickets = $order()->value('tickets');
verify(str_contains($tickets,'000123'),'pickup code delivered');
$gateway->detail['ticketcode'] = '';
$tick();
verify($order()->value('state') === 'issued' && $order()->value('tickets') === $tickets && $order()->value('next_check') < time()+86400,'incomplete detail and malformed showtime preserve issued ticket');
$gateway->detail['status'] = 10;
$tick();
verify($order()->value('state') === 'refund','confirmed closure sent to refund');
$order()->update(['state'=>'submitting','expires_at'=>time()-901]);
$gateway->detail = null;
$tick();
verify($order()->value('state') === 'review' && $gateway->creates === 1,'ambiguous submission reaches manual review without resubmission');
$pay()->update(['status'=>1]);
$order()->update(['state'=>'paying']);
$wechat->status = 'NOTPAY';
$tick();
verify($wechat->closes === 1 && $order()->value('state') === 'paying','expired prepay requests upstream closure');
$wechat->status = 'CLOSED';
$tick();
verify($order()->value('state') === 'expired' && (int)$pay()->value('status') === -1,'closed unpaid order expires permanently');

// Exercise the real SDK payload filter without sending a WeChat request.
$event = new CorePayEventService();
foreach (['site_id'=>$site,'type'=>'wechatpay','channel'=>'weapp','config'=>[]] as $field=>$value) {
    (new ReflectionProperty($event,$field))->setValue($event,$value);
}
$config = (new ReflectionProperty(core\pay\PayLoader::class,'config'))->getValue($event->app('refund'));
$wire = Yansongda\Artful\filter_params(new Yansongda\Supports\Collection(['notify_url'=>$config['notify_url'] ?? null]))->all();
verify(!isset($wire['notify_url']),'hostless CLI refund omits callback from request body');

Db::name('member')->insert(['member_id'=>7,'site_id'=>$site,'commission'=>0]);
$source = ['source'=>'jtk','order_sn'=>'withdrawn','site_id'=>$site,'member_id'=>7,'status'=>4,'upstream_update_time'=>1,
    'commission'=>1,'member_commission'=>1,'title'=>'fixture','act_name'=>'','sid'=>'s100000m7'];
Db::name('baijia_cps_order')->insert(['status'=>3,'credited'=>1,'credited_amount'=>1]+$source);
$cps = new CpsOrderService();
verify(!$cps->save($source),'withdrawn rebate is isolated for manual reconciliation');
verify((int)Db::name('baijia_cps_order')->where('order_sn','withdrawn')->value('credited') === 1,'failed reversal rolled back');
verify($cps->save(['order_sn'=>'next','member_id'=>0,'status'=>1]+$source),'following order can advance');
echo "PASS all reconciliation fixtures\n";
