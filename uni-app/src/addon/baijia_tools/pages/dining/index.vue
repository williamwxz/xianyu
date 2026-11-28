<template>
  <view class="page-wrap store-page">
    <view v-if="!brand" class="feedback">未找到点餐品牌</view>
    <template v-else>
      <view class="filters">
        <picker class="city-picker" :range="cities" range-key="name" :value="cityIndex" @change="changeCity">
          <view class="city">
            <image class="pin" src="/addon/baijia_tools/static/design/icon-location.png" mode="aspectFit" />
            <text class="city-name">{{ cities[cityIndex].name }}</text>
            <image class="arrow" src="/addon/baijia_tools/static/design/icon-arrow-down.png" mode="aspectFit" />
          </view>
        </picker>
        <view class="search">
          <image class="search-icon" src="/addon/baijia_tools/static/design/icon-search.png" mode="aspectFit" />
          <input v-model="keyword" class="query" placeholder="搜索门店名或地址" confirm-type="search" />
        </view>
      </view>

      <view class="section-head">
        <text class="heading">到店自取</text>
        <text v-if="!loading && !error" class="count">{{ filtered.length }} 家门店</text>
      </view>
      <view v-if="loading" class="white-card feedback">正在查找门店…</view>
      <view v-else-if="error" class="white-card feedback">
        <text>门店加载失败</text>
        <button class="retry" @click="load">重新加载</button>
      </view>
      <view v-else-if="!filtered.length" class="white-card feedback">
        {{ keyword.trim() ? '没有匹配的门店' : '当前城市暂无可点餐门店' }}
      </view>
      <view v-else v-for="store in filtered.slice(0, limit)" :key="store.id" class="white-card store-card" @click="select(store)">
        <view class="identity">
          <image v-if="brand.img" class="logo" :src="`/addon/baijia_tools/static/design/${brand.img}`" mode="aspectFit" />
          <text v-else class="logo brand-initial" :style="{ color: brand.color }">{{ brand.icon }}</text>
          <view class="details">
            <view class="store-name">{{ store.name }}</view>
            <view v-if="store.address" class="address">{{ store.address }}</view>
          </view>
        </view>
        <view class="actions">
          <button v-if="store.open" class="menu-button" hover-class="pressed" @click.stop="select(store)">预览菜单</button>
          <text v-else class="closed">休息中</text>
        </view>
      </view>
      <button v-if="filtered.length > limit" class="retry" @click="limit += 30">更多门店</button>
    </template>
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
const limit = ref(30)
const stores = ref([])
const loading = ref(true)
const error = ref(false)
const filtered = computed(() => stores.value.filter(s => (s.name + s.address).includes(keyword.value.trim())))
let version = 0
async function load() {
  const current = ++version
  loading.value = true
  error.value = false
  limit.value = 30
  stores.value = []
  try {
    const kfc = brand.value.id === 'kfc'
    const city = cities[cityIndex.value]
    const res = await request.get(kfc ? 'baijia_tools/native/kfc' : 'baijia_tools/dining/catalog',
      { brand: brand.value.id, city: kfc ? city.name : city.id }, { showErrorMessage: false })
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
@import "../../styles/base.css";
.store-page { padding-bottom: calc(40rpx + env(safe-area-inset-bottom)); }
.filters, .city, .search, .identity, .actions { display: flex; align-items: center; }
.filters { gap: 18rpx; }
.city-picker { max-width: 42%; }
.city { gap: 6rpx; height: 76rpx; font-size: 28rpx; font-weight: 650; }
.city-name { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
.pin, .arrow, .search-icon, .logo { flex-shrink: 0; }
.pin { width: 32rpx; height: 32rpx; }
.arrow { width: 18rpx; height: 18rpx; }
.search { flex: 1; min-width: 0; padding: 0 22rpx; gap: 12rpx; height: 76rpx; border-radius: 40rpx; background: #fff; border: 2rpx solid #e4e3db; }
.search-icon { width: 30rpx; height: 30rpx; }
.query { flex: 1; min-width: 0; height: 100%; font-size: 26rpx; }
.count, .closed { font-size: 24rpx; color: #62675f; }
.store-card { margin-bottom: 16rpx; padding: 28rpx; border: 1rpx solid #e9e7df; }
.identity { align-items: flex-start; gap: 20rpx; }
.logo { width: 76rpx; height: 76rpx; }
.brand-initial { text-align: center; line-height: 76rpx; background: #f7f6f0; border-radius: 18rpx; font-weight: 700; }
.details { flex: 1; min-width: 0; overflow-wrap: anywhere; }
.store-name { font-size: 32rpx; font-weight: 700; line-height: 1.4; }
.address { margin-top: 8rpx; color: #62675f; font-size: 25rpx; line-height: 1.55; }
.actions { justify-content: flex-end; margin-top: 18rpx; }
.menu-button, .retry { margin: 0; padding: 0 28rpx; border-radius: 36rpx; font-size: 26rpx; font-weight: 650; line-height: 68rpx; }
.menu-button { color: #202120; background: #d4fa20; }
.pressed { opacity: .8; }
.feedback { padding: 48rpx 24rpx; text-align: center; color: #62675f; }
.retry { margin: 18rpx auto 0; width: fit-content; background: #f3f3ec; }
</style>
