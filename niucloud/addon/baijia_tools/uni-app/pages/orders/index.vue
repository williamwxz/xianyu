<template>
  <view class="page-wrap">
    <view class="summary">
      <view class="sum-item"><text class="sum-num">{{ money(summary.credited) }}</text><text class="sum-name">已到账返利</text></view>
      <view class="sum-item"><text class="sum-num">{{ money(summary.pending) }}</text><text class="sum-name">待到账返利</text></view>
      <view class="sum-item"><text class="sum-num">{{ summary.order_count ?? '--' }}</text><text class="sum-name">有效订单</text></view>
    </view>
    <view class="rule">通过本小程序进入美团、聚推客合作活动下单，订单结算后返利自动转入「佣金」，可在「我的 → 佣金」提现。订单同步约有 10 分钟延迟，结算时间以合作平台为准。</view>

    <view class="tabs">
      <button v-for="t in tabs" :key="t.key" class="tab" :class="{ active: tab === t.key }" @click="switchTab(t.key)">{{ t.name }}</button>
    </view>

    <view v-for="o in list" :key="o.id" class="order white-card">
      <view class="order-top"><text class="order-source">{{ o.source_name }}{{ o.act_name && o.source !== 'meituan' ? ' · ' + o.act_name : '' }}</text><text class="order-status" :class="'s' + o.status">{{ o.status_name }}</text></view>
      <text class="order-title">{{ o.title || o.act_name || '订单' }}</text>
      <view class="order-bottom">
        <text class="order-meta">{{ o.pay_time }} · 实付 ¥{{ o.pay_amount }}</text>
        <text class="order-rebate" v-if="o.status != 4">{{ o.credited == 1 ? '已返' : '预计返' }} ¥{{ o.member_commission }}</text>
      </view>
    </view>

    <view v-if="loaded && !list.length" class="empty">还没有订单。去首页领券、点外卖或买团购，下单后会显示在这里。</view>
    <button v-if="hasMore" class="more" @click="load(false)">加载更多</button>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import request from '@/utils/request'
import useMemberStore from '@/stores/member'
import { useLogin } from '@/hooks/useLogin'

const tabs = [{ key: '', name: '全部' }, { key: 'pending', name: '待结算' }, { key: 'settled', name: '已结算' }, { key: 'invalid', name: '已失效' }]
const tab = ref('')
const list = ref([])
const page = ref(1)
const hasMore = ref(false)
const loaded = ref(false)
const summary = ref({})
const money = (v) => (v === undefined || v === null) ? '--' : Number(v).toFixed(2)

const load = async (reset) => {
  if (reset) { page.value = 1; list.value = [] }
  const res = await request.get('baijia_tools/cps/orders', { tab: tab.value, page: page.value })
  const data = res.data || {}
  list.value = list.value.concat(data.list || [])
  hasMore.value = Boolean(data.has_more)
  page.value += 1
  loaded.value = true
}
const switchTab = (key) => { tab.value = key; load(true) }

onShow(() => {
  if (!useMemberStore().token) { useLogin().setLoginBack({ url: '/addon/baijia_tools/pages/orders/index' }); return }
  request.get('baijia_tools/cps/summary').then((res) => { summary.value = res.data || {} })
  load(true)
})
</script>

<style scoped>
@import "../../styles/base.css";
.summary { display: flex; padding: 32rpx 0; border-radius: 24rpx; background: linear-gradient(120deg, #ff4c30, #ff9a3c); color: #fff; }
.sum-item { flex: 1; text-align: center; }
.sum-num { display: block; font-size: 38rpx; font-weight: 800; }
.sum-name { display: block; font-size: 23rpx; opacity: .9; margin-top: 6rpx; }
.rule { font-size: 23rpx; color: #999; line-height: 1.7; padding: 18rpx 6rpx; }
.tabs { display: flex; gap: 14rpx; margin-bottom: 18rpx; }
.tab { flex: 1; height: 64rpx; line-height: 64rpx; border-radius: 32rpx; background: #fff; color: #666; font-size: 25rpx; }
.tab.active { background: #ff5a2c; color: #fff; font-weight: 600; }
.order { padding: 24rpx; margin-bottom: 18rpx; }
.order-top { display: flex; justify-content: space-between; font-size: 23rpx; }
.order-source { color: #999; }
.order-status { color: #ff7a1c; }
.order-status.s3 { color: #13a06a; }
.order-status.s4 { color: #aaa; }
.order-title { display: block; font-size: 28rpx; font-weight: 600; color: #333; margin: 12rpx 0; }
.order-bottom { display: flex; justify-content: space-between; align-items: center; }
.order-meta { font-size: 23rpx; color: #999; }
.order-rebate { font-size: 26rpx; color: #ff4c30; font-weight: 700; }
.empty { text-align: center; color: #aaa; font-size: 25rpx; line-height: 1.8; padding: 80rpx 40rpx; }
.more { margin: 10rpx 0 30rpx; background: #fff; color: #666; font-size: 26rpx; border-radius: 40rpx; }
</style>
