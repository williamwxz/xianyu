<template>
  <view class="page-wrap">
    <view v-if="order" class="card">
      <view class="status">{{ order.status }}</view>
      <view class="title">{{ order.title }}</view>
      <view>{{ order.cinema }} · {{ order.time }}</view>
      <view>{{ order.seat }}</view>
      <view class="amount">订单金额 ¥{{ order.amount }}</view>
      <view class="serial" selectable>订单号 {{ order.id }}</view>
      <view v-if="order.tickets" class="tickets">
        <view class="title">取票凭证</view>
        <view v-for="code in order.tickets.ticketcode" :key="code" selectable>取票码 {{ code }}</view>
        <image v-for="url in order.tickets.ticketimage" :key="url" :src="url" mode="widthFix" @click="preview(url)" />
        <view v-if="order.tickets.realseat.length">实际座位 {{ order.tickets.realseat.join('、') }}</view>
      </view>
      <button v-if="order.can_pay" class="pay" :disabled="busy" @click="pay">微信支付 ¥{{ order.amount }}</button>
      <view v-else-if="order.state === 'quoted'">体验验收中，本笔付款须经商户确认。</view>
    </view>
    <button class="refresh" :disabled="busy" @click="load">刷新订单状态</button>
  </view>
</template>
<script setup>
import { ref } from 'vue'
import { onLoad, onShow, onHide, onUnload } from '@dcloudio/uni-app'
import request from '@/utils/request'
const order = ref(null), busy = ref(false)
let sn = '', timer, active = false, reading = false
function stop() { clearTimeout(timer) }
async function load() {
  if (!sn || reading) return
  reading = true
  stop()
  try { order.value = (await request.get('baijia_tools/movie/detail',{sn})).data }
  finally {
    reading = false
    if (active && ['paying','submitting','pending','refund'].includes(order.value?.state)) timer = setTimeout(load,10000)
  }
}
async function pay() {
  if (busy.value) return
  busy.value = true
  try {
    const {data} = await request.post('baijia_tools/movie/pay',{sn})
    await new Promise(resolve => uni.requestPayment({provider:'wxpay',...data,complete:resolve}))
  } finally { busy.value = false; await load() }
}
function preview(url) { uni.previewImage({current:url,urls:order.value.tickets.ticketimage}) }
onLoad(q => { sn = q.sn })
onShow(() => {active=true; load()})
onHide(() => {active=false; stop()})
onUnload(() => {active=false; stop()})
</script>
<style scoped>
@import "../../styles/base.css";
.card { background:#fff; padding:32rpx; border-radius:24rpx; line-height:1.85; font-size:26rpx }
.status { color:#51691f; font-weight:700 }
.title,.amount { font-size:34rpx; font-weight:650 }
.serial,.refresh { color:#666; font-size:24rpx }
.tickets { border-top:2rpx dashed #d3d8c4; margin-top:24rpx; padding-top:20rpx }
.tickets image { width:340rpx; max-width:100%; display:block }
.pay { background:#d4fa20; border-radius:36rpx }
</style>
