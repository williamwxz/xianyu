# 美团联盟直连（1.1.1）

2026-11-11（America/Los_Angeles）接入。媒体 ID：1000662720。

## 功能

- 团购页：今日必推（2）、实时热销（5）、同城优选（3）真实到店餐饮榜单。
- 城市选择使用美团官方 2025 城市字典，共 359 项；默认北京，并明确显示城市。选择保存在本机。不请求定位权限。
- 商品搜索是美团全品类搜索，使用 searchId 游标加载下一页。榜单固定最多 20 条，不做假分页。
- 商品价格单位是元；展示联盟销量、城市可用门店数量。购买跳转使用官方转链，微信打开美团小程序，H5 跳转官方链接。
- sid 由服务端站点 ID / 登录会员 ID 生成，客户端不能指定。不包含订单同步、佣金记账、提现或回调接口。
- 原有聚推客活动入口保留。

## 服务端配置

仅在服务器 niucloud 根目录 `.env` 配置；不要把真实值写入仓库、前端或本文档：

```ini
[MEITUAN]
APPKEY = <media appkey>
APPSECRET = <media signing secret>
```

`GET /api/baijia_tools/meituan/products`：city_id、topic（2/3/5）或 keyword，可选 cursor。
`GET /api/baijia_tools/meituan/link`：id（productViewSign）、platform、biz_line。
使用项目既有 site-id、channel、token 请求头。商品缓存 120 秒，会员维度转链缓存 300 秒。
签名对实际发送的 JSON 字节计算 Content-MD5，再按官方规则计算 HMAC-SHA256。上游异常不向客户端暴露密钥或请求细节。

源码以本 addon 的 `uni-app/` 为准，构建前同步至 `uni-app/src/addon/baijia_tools/`。

## 验证与部署

- 原有 Node 测试 13 项通过；PHP 8.2 语法检查及 `php tests/meituan.test.php` 通过。
- 线上 HTTPS：三个榜单各 20 条；搜索首、次页各 20 条；商品小程序/H5 转链成功；非法城市、榜单、超长关键词被拒绝；原有 jtk/acts 正常。
- H5 手机宽度视觉检查：真实商品卡片正常、搜索加载更多由 20 增至 40 条，无横向溢出。
- H5、mp-weixin 构建通过；源码与两种产物扫描均未发现本次 API 凭证。
- 服务端接口和 H5 已部署到 mini.baijia.life。备份：`/root/baijia-meituan-backup-20261112-142154/`（env、addon、wap）。
- 微信开发版 1.1.1 已由官方 CLI 上传成功：总包 2,748,611 字节，主包 1,693,224 字节。
- 尚未切换体验版、提交审核或正式发布；真机跳转及实际订单归因尚待验证。
- 新增代码与此前工作均未提交 Git。

## 官方参考

- https://page.meituan.net/html/1687318722216_edeb3f/index.html （签名）
- https://media.meituan.com/pc/index.html （物料推广 → API 接口 → 商品查询 / 获取推广链接）
- https://s3plus.meituan.net/media-public/城市字典2025.xlsx （城市 ID 来源）

回滚时恢复备份 addon、env、wap；注意先确认此后没有新部署，以免覆盖后续工作。
