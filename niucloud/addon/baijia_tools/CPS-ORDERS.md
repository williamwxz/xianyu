# 订单同步与会员返利（2026-11-14 上线）

## 流程

1. 转链时 sid = `s{site_id}m{member_id}`（游客 `s{site_id}g`），由服务端生成。
2. 计划任务 `baijia_cps_order_sync` 每 10 分钟按更新时间拉取聚推客 `/union/orders` 与美团 `query_order`（platform 1、2），每次回看 15 分钟。游标存 Cache（`baijia_cps_synced_{source}`）；首次回看 3 天，单次最多追 2 天。
3. 订单 upsert 到 `{prefix}baijia_cps_order`，唯一键 (source, order_sn)。
4. 状态变为已结算（聚推客 3 / 美团 6）且有会员时，给会员 `commission` 账户记 `cps_rebate`。已入账后失效则记 `cps_rebate_refund` 扣回；余额不足时整单回滚并写日志，需人工处理。

## 返利公式

会员返利 = 平台佣金 × 净得系数 × MEMBER_RATE，向下取整到分。
净得系数：聚推客 0.8（平台抽 10%，部分活动再抽 10%），美团 1.0。

```ini
[CPS]
MEMBER_RATE = 0.5   ; 不配置时默认 0.5
```

## 接口

- `GET /api/baijia_tools/cps/orders?tab=&page=`（需登录）：tab 取值 pending / settled / invalid。
- `GET /api/baijia_tools/cps/summary`（需登录）。
- 前端页面：`pages/orders/index`，入口在「我的 → 订单中心 → 返利订单」。

## 部署

`scripts/deploy-baijia.sh` 依次执行：备份、rsync、`baijia-post-deploy.php`（建表、登记计划任务）、重启 `niucloud-worker`。worker 只在启动时加载任务，所以新增或修改任务后必须重启。
手动同步一次：`sudo -u www php -r 'require "vendor/autoload.php"; (new think\App())->initialize(); var_dump((new addon\baijia_tools\app\service\core\CpsOrderService())->sync());'`

## 未做

邀请好友、粉丝、二级分佣；美团订单推送回调；后台订单管理页；H5 重新构建。
