<template>
  <view v-if="!brand" class="note">未找到点餐品牌</view>
  <view v-else class="dining">
    <view class="eyebrow">{{ brand?.name }} · 到店自取</view>
    <picker :range="cities" range-key="name" :value="cityIndex" @change="changeCity">
      <view class="title">{{ cities[cityIndex].name }} ⌄</view>
    </picker>
    <input v-model="keyword" class="find" placeholder="搜索门店名或地址" />
    <view v-if="loading" class="note">正在查找门店…</view>
    <button v-else-if="error" @click="load">门店加载失败，点击重试</button>
    <view v-else-if="!filtered.length" class="note">{{ keyword ? '没有匹配的门店' : '当前城市暂无可点餐门店' }}</view>
    <view v-else v-for="store in filtered" :key="store.id" class="card" @click="select(store)">
      <view class="name">{{ store.name }}</view>
      <view class="muted">{{ store.address }}</view>
      <text class="status">{{ store.open ? '选餐 ›' : '休息中' }}</text>
    </view>
  </view>
</template>
<script setup>
import { ref, computed } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import request from '@/utils/request'
import cities from '../../lib/meituan-cities.json'
import { getService } from '../../lib/services.mjs'
const brand = ref(null)
const cityIndex = ref(0)
const keyword = ref('')
const stores = ref([])
const loading = ref(true)
const error = ref(false)
const filtered = computed(() => stores.value.filter(s => (s.name + s.address).includes(keyword.value.trim())))
let version = 0
async function load() {
  const current = ++version
  loading.value = true
  error.value = false
  stores.value = []
  try {
    const res = await request.get('baijia_tools/dining/catalog', { brand: brand.value.id, city: cities[cityIndex.value].id }, { showErrorMessage: false })
    if (version === current) stores.value = res.data.stores
  } catch { if (version === current) error.value = true }
  finally { if (version === current) loading.value = false }
}
function changeCity(e) {
  if (!brand.value || !cities[Number(e.detail.value)]) return
  cityIndex.value = Number(e.detail.value)
  keyword.value = ''
  load()
}
function select(store) {
  if (store.open) uni.navigateTo({ url: './menu?brand=' + brand.value.id + '&city=' + cities[cityIndex.value].id + '&store=' + encodeURIComponent(store.id) })
}
onLoad(query => {
  const service = getService(query?.brand)
  if (!service?.brand || service.jtk) return
  brand.value = service
  uni.setNavigationBarTitle({ title: brand.value.name + ' · 选门店' })
  const saved = uni.getStorageSync('baijia_meituan_city') || 'WKV2HMXUEK634WP64CUCUQGM64'
  cityIndex.value = Math.max(0, cities.findIndex(c => c.id === saved))
  load()
})
</script>
<style scoped>
@import "./style.css";
</style>
