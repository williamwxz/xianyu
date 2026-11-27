# 蚂蚁星球接入

小程序「首页 → 更多 → 蚂蚁星球」提供话费电费和美团券包。「我的订单 → 蚂蚁星球订单」按登录会员查询充值、点餐及影票订单。点餐和电影入口仍使用的确省页面。订单列表只展示使用同一跟单 UID 的记录。

在服务端 niucloud 的 .env 中设置以下项目，保存后通过正常的配置刷新流程生效。

```ini
[HAOJINGKE]
SITE_ID = 100000
APIKEY = <供应商分配的推广标识>
SECRET = <供应商签名密钥>
UID_KEY = <本地生成的稳定随机密钥>
```

用 openssl rand -hex 32 生成 UID_KEY，保存在服务器配置和备份中。更改此值会改变会员对应的跟单 UID。半屏页面接收 APIKEY 和跟单 UID。SECRET 和 UID_KEY 只供服务器使用。

微信公众平台「设置 → 第三方设置 → 半屏小程序管理」添加 wx5f482af87ff127ca，确认对应商户已获充值及券包权限。上传新开发版后，从首页打开充值服务，返回小程序核对查单归属、状态和金额。

[半屏服务及接入要求](https://www.haojingke.com/default/pluginembedded) · [订单查询及签名](https://www.haojingke.com/index.php/default/plugin)
