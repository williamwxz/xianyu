<template>
  <view class="about">
    <text class="kicker">百迦好省 / 1.0.1</text>
    <text class="heading">把优惠，算明白。</text>
    <text class="lead">日常购物的价格计算工具。输入金额与优惠规则，比较预计到手价。</text>
    <view v-for="section in sections" :key="section.title" class="section"><text class="section-title">{{ section.title }}</text><text class="body">{{ section.body }}</text></view>
    <view class="section privacy"><text class="section-title">隐私与本地记录</text><text v-for="paragraph in privacy" :key="paragraph" class="body privacy-paragraph">{{ paragraph }}</text></view>
    <text class="footnote">优惠以商家实际结算为准。当前版本提供计算功能，不提供领券、下单、返佣或提现服务。</text>
    <button class="back" @click="back">返回计算</button>
  </view>
</template>
<script setup>
const privacy = [
  '本版本不提供登录，不主动采集姓名、手机号、位置或支付信息，也不调用我们的服务器接口。计算在设备本地完成。',
  '只有点击“保存本次计算”，才会把输入金额、规则、结果及保存时间存到当前设备，最多保留最近 20 条。可在首页点击“清空记录”删除。记录不会同步到其他设备，清除小程序数据后可能丢失。',
  '本版本未接入广告或第三方统计服务。微信平台自身的数据处理以其隐私政策为准。'
]
const sections = [
  { title: '01  折扣计算', body: '到手价 = 商品金额 × 折扣 ÷ 10。例如 199 元打 8.5 折，到手价为 169.15 元。“减 15%”等同于“8.5 折”，不要将 85 填入折扣栏。' },
  { title: '02  满减优惠', body: '只减一次：达到门槛后抵扣一次。\n每满减：每达到一次门槛就抵扣一次。例如 650 元每满 200 减 30，生效 3 次，到手价为 560 元。计算采用金额门槛，不处理商品数量条件或特定品类限制。' },
  { title: '03  券后到手价', body: '先计算折后金额，再按折后金额判断用券门槛，最后减去券额，最低到手价为 0 元。无折扣填 10，无门槛填 0。\n例如 200 元打 8 折后为 160 元，用满 150 减 20 的券，到手价为 140 元。请先确认商家支持这样的叠加顺序。' },
  { title: '04  金额精度', body: '金额最多保留两位小数，单项最高 999,999 元。折扣范围为大于 0 至 10，最多保留两位小数。折后金额四舍五入到分；节省比例与等效折扣为近似值。计算不包含运费、税费、积分或返现。' }
]
function back() { uni.switchTab({ url: '/pages/calculator/index' }) }
</script>
<style scoped>
.about { max-width: 960rpx; margin: 0 auto; padding: 42rpx 32rpx calc(40rpx + env(safe-area-inset-bottom)); }
.kicker { font-size: 22rpx; letter-spacing: 2rpx; color: #b68e7a; }
.heading { display: block; font-size: 47rpx; font-weight: 700; margin: 20rpx 0; }
.lead { display: block; color: #888888; font-size: 27rpx; line-height: 1.8; margin-bottom: 32rpx; }
.section { border-radius: 22rpx; background: white; border: 1rpx solid #eeeeee; padding: 28rpx; margin-bottom: 20rpx; }
.section-title { font-size: 29rpx; font-weight: 600; display: block; margin-bottom: 18rpx; }
.body { display: block; white-space: pre-line; font-size: 26rpx; line-height: 1.9; color: #888888; }
.privacy { background: #fff2e6; }
.privacy-paragraph + .privacy-paragraph { margin-top: 20rpx; }
.footnote { display: block; font-size: 23rpx; line-height: 1.8; color: #aaaaaa; padding: 12rpx 0 28rpx; }
.back { background: #ff5838; color: #fff; font-size: 28rpx; border-radius: 14rpx; padding: 20rpx; line-height: 1.5; }
</style>
