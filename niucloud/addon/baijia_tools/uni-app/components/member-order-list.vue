<template>
  <view class="group">
    <view class="label">{{ title }}</view>
    <view v-for="order in rows" :key="order.id" class="entry">
      <view class="line"><text class="name">{{ order.title || order.act_name || title }}</text><text>{{ order.status_name || order.status }}</text></view>
      <view v-if="promotion" class="detail">
        {{ order.pay_time }} · 实付 ¥{{ order.pay_amount }}
        <text v-if="Number(order.promoter_credited) === 1"> · 已到账 ¥{{ order.promoter_credited_amount }}</text>
        <text v-else-if="Number(order.promoter_credited) === 2"> · 已失效</text>
        <text v-else-if="[1,2,3].includes(Number(order.status))"> · 推广佣金 ¥{{ order.promoter_commission }}</text>
      </view>
      <view v-else class="detail">
        <view>订单号 {{ order.id }}</view>
        <view v-if="order.amount != null">实付 ¥{{ order.amount }}</view>
        <view v-if="order.pickup" class="pickup">取餐码 {{ order.pickup }}</view>
      </view>
    </view>
    <text v-if="loading" class="detail">加载中…</text>
    <button v-else-if="failed" @click="load">加载失败，点击重试</button>
    <button v-else-if="more" @click="load">加载更多{{ title }}</button>
    <text v-else-if="!rows.length" class="detail">暂无{{ title }}</text>
  </view>
</template>
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import request from '@/utils/request'
const props = defineProps({ title: String, kind: String, promotion: Boolean })
const rows = ref([])
const more = ref(false)
const failed = ref(false)
const loading = ref(false)
let page = 1
let active = true
async function load() {
  if (loading.value) return
  loading.value = true
  failed.value = false
  try {
    const url = props.promotion ? 'baijia_tools/distribution/orders' : 'baijia_tools/mayi/orders'
    const params = props.promotion ? { page } : { page, kind: props.kind }
    const { data } = await request.get(url, params, { showErrorMessage: false })
    if (!active) return
    const ids = new Set(rows.value.map(row => row.id))
    rows.value.push(...data.list.filter(row => !ids.has(row.id)))
    more.value = data.has_more
    page++
  } catch {
    if (active) failed.value = true
  } finally {
    if (active) loading.value = false
  }
}
onMounted(load)
onUnmounted(() => { active = false })
</script>
<style scoped>
.group { margin: 20rpx 0; background: #fff; border-radius: 22rpx; padding: 24rpx; }
.label, .name, .pickup { font-weight: 650; }
.label { font-size: 30rpx; margin-bottom: 12rpx; }
.entry { padding: 18rpx 0; border-top: 1rpx solid #eeeee8; }
.line { display: flex; justify-content: space-between; gap: 16rpx; font-size: 26rpx; }
.detail { color: #60645d; font-size: 24rpx; line-height: 1.65; overflow-wrap: anywhere; }
button { font-size: 25rpx; border-radius: 32rpx; background: #f5f5ef; }
</style>
