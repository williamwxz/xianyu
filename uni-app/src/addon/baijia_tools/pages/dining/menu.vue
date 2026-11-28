<template>
  <view class="dining">
    <view v-if="loading" class="note">正在加载菜单…</view>
    <button v-else-if="error" @click="load">菜单加载失败，点击重试</button>
    <template v-else-if="store">
      <view class="eyebrow">{{ brandName }} · {{ reviewing ? '核对餐品' : '到店自取' }}</view>
      <view class="title">{{ store.name }}</view>
      <view class="muted address">{{ store.address }}</view>
      <view v-if="!store.open" class="note">门店休息中</view>
      <view v-else-if="!items.length" class="note">暂无可选餐品</view>
      <view v-for="item in displayed" :key="item.id" class="card">
        <view class="name">{{ item.name }}</view>
        <view class="muted">{{ item.spec }}</view>
        <view class="line">
          <text>¥{{ yuan(item.price_fen) }}</text>
          <text v-if="reviewing">× {{ quantity(item) }}</text>
          <view v-else-if="available(item) && store.open" class="stepper">
            <button :disabled="!quantity(item)" @click="adjust(item, -1)">−</button>
            <text>{{ quantity(item) }}</text>
            <button :disabled="item.stock != null && quantity(item) >= item.stock" @click="adjust(item, 1)">＋</button>
          </view>
          <text v-else class="muted">暂不可选</text>
        </view>
      </view>
      <view v-if="selected.length" class="bar">
        <view>餐品合计 ¥{{ yuan(total) }}<view v-if="reviewing" class="muted">当前仅供预览，尚未开放支付</view></view>
        <button @click="reviewing = !reviewing">{{ reviewing ? '返回选餐' : '核对餐品' }}</button>
      </view>
    </template>
  </view>
</template>
<script setup>
import { ref, computed } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import request from '@/utils/request'
import { getService } from '../../lib/services.mjs'
const store = ref(null)
const items = ref([])
const counts = ref(new Map())
const reviewing = ref(false)
const loading = ref(true)
const error = ref(false)
const brandName = ref('')
let params
const yuan = fen => (fen / 100).toFixed(2)
const available = item => item.stock == null ? item.available === true : item.stock > 0
const quantity = item => counts.value.get(item.id) || 0
function adjust(item, delta) {
  counts.value.set(item.id, Math.max(0, Math.min(item.stock ?? Number.MAX_SAFE_INTEGER, quantity(item) + delta)))
}
const selected = computed(() => items.value.filter(item => quantity(item) > 0))
const total = computed(() => selected.value.reduce((sum, item) => sum + quantity(item) * item.price_fen, 0))
const displayed = computed(() => reviewing.value ? selected.value : items.value)
async function load() {
  loading.value = true
  error.value = false
  try {
    if (!params.store) throw new Error('缺少门店编号')
    const { data } = await request.get(params.brand === 'kfc' ? 'baijia_tools/native/kfc' : 'baijia_tools/dining/catalog', params, { showErrorMessage: false })
    if (!data.store || !Array.isArray(data.items)) throw new Error('门店菜单无效')
    store.value = data.store
    items.value = data.items
  } catch { error.value = true }
  finally { loading.value = false }
}
onLoad(query => {
  params = { brand: query?.brand, city: query?.city, store: query?.store }
  // The WeChat page receives the raw query. The H5 router decodes it itself.
  // #ifdef MP-WEIXIN
  try { params.store = decodeURIComponent(params.store || '') }
  catch { params.store = '' }
  // #endif
  brandName.value = getService(params.brand)?.name || ''
  load()
})
</script>
<style scoped>
@import "./style.css";
.address { margin: -12rpx 0 24rpx; }
.line, .bar, .stepper { display: flex; align-items: center; justify-content: space-between; }
.line { margin-top: 18rpx; }
.stepper { gap: 16rpx; }
.stepper button { margin: 0; padding: 0; width: 64rpx; line-height: 56rpx; border-radius: 16rpx; }
.bar { position: fixed; bottom: 0; left: 0; right: 0; padding: 20rpx 28rpx calc(20rpx + env(safe-area-inset-bottom)); background: white; }
.bar button { margin: 0; border-radius: 44rpx; background: #202120; color: #d4fa20; font-size: 28rpx; }
</style>
