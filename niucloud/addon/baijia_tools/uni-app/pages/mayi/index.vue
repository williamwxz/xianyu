<template>
  <view class="page-wrap">
    <button v-if="!member.token" class="login" @click="login">登录使用生活服务和查询订单</button>
    <template v-else>
      <view v-if="!ordersOnly" class="services">
        <!-- #ifdef MP-WEIXIN -->
        <button v-for="item in services" :key="item.name" :disabled="!launch" @click="open(item)">{{ item.name }} ›</button>
        <text v-if="launchError" class="hint" @click="prepare">{{ launchError }} · 点击重试</text>
        <!-- #endif -->
        <!-- #ifndef MP-WEIXIN -->
        <text class="hint">请在微信小程序内打开生活服务</text>
        <!-- #endif -->
      </view>
      <view class="heading">我的蚂蚁星球订单</view>
      <view class="tabs">
        <button v-for="tab in tabs" :key="tab.id" :class="{active:kind === tab.id}" @click="switchTab(tab.id)">{{ tab.name }}</button>
      </view>
      <view v-for="order in orders" :key="order.id" class="card">
        <view class="heading">{{ order.title }}</view>
        <view>{{ order.status }} <text v-if="order.amount !== null"> · 实付 ¥{{ order.amount }}</text></view>
        <view v-if="order.pickup">取餐码 {{ order.pickup }}</view>
        <text class="hint">订单号 {{ order.id }}</text>
      </view>
      <text v-if="loading" class="hint">正在加载…</text>
      <button v-else-if="error" @click="load(false)">{{ error }} · 重试</button>
      <text v-else-if="!orders.length" class="hint">暂无关联订单</text>
      <button v-else-if="more" @click="load(false)">加载更多</button>
    </template>
  </view>
</template>
<script setup>
import { ref } from 'vue'
import { onLoad, onShow } from '@dcloudio/uni-app'
import request from '@/utils/request'
import useMemberStore from '@/stores/member'
import { useLogin } from '@/hooks/useLogin'
const member = useMemberStore()
const ordersOnly = ref(false)
const services = [
  { name: '话费电费', path: 'pages/recharge/recharge' },
  { name: '美团券包', path: 'plugin-private://wx8b412290993bf26a/pages/coupon/index' }
]
const tabs = [{ id: 'recharge', name: '充值' }, { id: 'dining', name: '点餐' }, { id: 'movie', name: '电影' }]
const kind = ref('recharge')
const orders = ref([])
const launch = ref(null)
const more = ref(false)
const loading = ref(false)
const error = ref('')
const launchError = ref('')
let page = 1
let version = 0
let launchVersion = 0
const login = () => useLogin().setLoginBack({ url: '/addon/baijia_tools/pages/mayi/index' + (ordersOnly.value ? '?orders=1' : '') })
async function prepare() {
  const v = ++launchVersion
  launch.value = null
  launchError.value = ''
  try {
    const { data } = await request.get('baijia_tools/mayi/launch', {}, { showErrorMessage: false })
    if (v === launchVersion) launch.value = data
  } catch (e) { if (v === launchVersion) launchError.value = e?.msg || '生活服务暂不可用' }
}
function open(item) {
  // #ifdef MP-WEIXIN
  if (!launch.value) return
  if (!wx.openEmbeddedMiniProgram) return uni.showToast({ title: '请升级微信后重试', icon: 'none' })
  const { apikey, uid } = launch.value
  wx.openEmbeddedMiniProgram({
    appId: 'wx5f482af87ff127ca',
    path: item.path + '?apikey=' + encodeURIComponent(apikey) + '&uid=' + encodeURIComponent(uid) + '&index=/addon/baijia_tools/pages/home/index',
    fail: e => { if (!e.errMsg?.includes('cancel')) uni.showToast({ title: '暂时无法打开该服务', icon: 'none' }) }
  })
  // #endif
}
async function load(reset) {
  if (!reset && loading.value) return
  if (reset) { page = 1; more.value = false; orders.value = [] }
  const v = ++version
  loading.value = true
  error.value = ''
  try {
    const { data } = await request.get('baijia_tools/mayi/orders', { kind: kind.value, page }, { showErrorMessage: false })
    if (v !== version) return
    const ids = new Set(orders.value.map(o => o.id))
    orders.value.push(...data.list.filter(o => !ids.has(o.id)))
    more.value = data.has_more
    page++
  } catch (e) { if (v === version) error.value = e?.msg || '查单暂不可用' }
  finally { if (v === version) loading.value = false }
}
function switchTab(id) { if (kind.value !== id) { kind.value = id; load(true) } }
onLoad(query => { ordersOnly.value = query?.orders === '1' })
onShow(() => {
  launchVersion++
  version++
  launch.value = null
  orders.value = []
  if (!member.token) return
  // #ifdef MP-WEIXIN
  if (!ordersOnly.value) prepare()
  // #endif
  load(true)
})
</script>
<style scoped>
@import "../../styles/base.css";
.services, .tabs { display: flex; gap: 12rpx; flex-wrap: wrap; margin: 20rpx 0; }
button { background: white; font-size: 26rpx; border-radius: 24rpx; }
.tabs button { flex: 1; }
button.active, .login { background: #202120; color: #d4fa20; }
.heading { font-weight: 700; margin: 20rpx 0 10rpx; }
.card { background: white; padding: 8rpx 24rpx 24rpx; border-radius: 24rpx; margin-bottom: 14rpx; }
.hint { display: block; color: #666; font-size: 24rpx; padding: 16rpx 0; }
</style>
