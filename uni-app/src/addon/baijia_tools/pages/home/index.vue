<template>
  <view class="home">
    <image class="hero" src="/addon/baijia_tools/static/design/hero.png" mode="widthFix" />
    <view class="header" :style="{ paddingTop: `${navTop}px` }">
      <view class="title-row" :style="{ height: `${navHeight}px` }"><text class="app-name">的确省</text></view>
      <view class="search-row">
        <picker mode="multiSelector" :range="[provinces, pickerNames]" :value="pickerValue" @cancel="resetPicker" @columnchange="changeColumn" @change="changeCity">
          <view class="city">
            <image class="city-icon" src="/addon/baijia_tools/static/design/icon-location.png" />
            <text class="city-name">{{ city.name }}</text>
            <image class="city-arrow" src="/addon/baijia_tools/static/design/icon-arrow-down.png" />
          </view>
        </picker>
        <view class="search" @click="browse(true)">
          <image class="search-icon" src="/addon/baijia_tools/static/design/icon-search.png" />
          <text>搜索商品、品牌和服务</text>
        </view>
      </view>
    </view>

    <view class="body">
      <view class="grid card">
        <view v-for="item in grid" :key="item.id" class="grid-item" @click="openService(item.id)">
          <image class="grid-icon" :src="`/addon/baijia_tools/static/design/${item.img}`" mode="aspectFit" />
          <text class="grid-name">{{ item.name }}</text>
        </view>
        <view class="grid-item" @click="showMore = !showMore">
          <image class="grid-icon" src="/addon/baijia_tools/static/design/grid/more.png" mode="aspectFit" />
          <text class="grid-name">{{ showMore ? '收起' : '更多' }}</text>
        </view>
        <view v-if="showMore" class="more-grid">
          <view v-for="item in more" :key="item.id" class="grid-item" @click="openService(item.id)">
            <view class="more-icon">{{ item.icon }}</view>
            <text class="grid-name">{{ item.name }}</text>
          </view>
        </view>
      </view>

      <view class="entries">
        <view class="entry entry-food" @click="openService('bawangcan')">
          <text class="entry-title">霸王餐</text>
          <text class="entry-sub">0元抢大餐</text>
          <text class="entry-go">GO</text>
          <image class="entry-art" src="/addon/baijia_tools/static/design/card-bawangcan.png" mode="aspectFit" />
        </view>
        <view class="entry entry-movie" @click="openService('movie')">
          <text class="entry-title">热门电影</text>
          <text class="entry-sub">特惠看大片</text>
          <text class="entry-go">GO</text>
          <image class="entry-art" src="/addon/baijia_tools/static/design/card-movie.png" mode="aspectFit" />
        </view>
      </view>

      <view class="section-bar">
        <view class="section-name"><image class="section-icon" src="/addon/baijia_tools/static/design/icon-meituan.png" /><text>美团到店</text></view>
        <view class="section-more" @click="browse(false)">更多 ›</view>
      </view>
      <ProductFeed :city-id="city.id" />
    </view>
  </view>
  <BjTabbar current="home" />
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { onLoad, onShareAppMessage } from '@dcloudio/uni-app'
import ProductFeed from '../../components/product-feed.vue'
import { getAddressByLatlng } from '@/app/api/system'
import { saveInviteReferrer } from '@/utils/interceptor'
import useSystemStore from '@/stores/system'
import BjTabbar from '../../components/bj-tabbar.vue'
import cityData from '../../lib/meituan-cities.json'
import { HOME_GRID, MORE_GRID, getService } from '../../lib/services.mjs'
import { openService } from '../../lib/launch.js'

const grid = HOME_GRID.map(getService)
const more = MORE_GRID.map(getService)
const showMore = ref(false)

// Custom nav bar: line the title up with the WeChat capsule button.
const sys = uni.getSystemInfoSync()
let navTop = sys.statusBarHeight || 20
let navHeight = 44
// #ifdef MP-WEIXIN
const capsule = uni.getMenuButtonBoundingClientRect()
if (capsule && capsule.height) { navHeight = capsule.height + (capsule.top - navTop) * 2 }
// #endif

const systemStore = useSystemStore()
const savedCity = uni.getStorageSync('baijia_meituan_city')
const city = ref(cityData.find(c => c.id === (savedCity || 'WKV2HMXUEK634WP64CUCUQGM64')) || cityData[0])
const provinces = [...new Set(cityData.map(c => c.province))]
const pickerValue = ref([0, 0])
const pickerCities = computed(() => cityData.filter(c => c.province === provinces[pickerValue.value[0]]))
const pickerNames = computed(() => pickerCities.value.map(c => c.name))
function resetPicker() {
  const cities = cityData.filter(c => c.province === city.value.province)
  pickerValue.value = [provinces.indexOf(city.value.province), cities.findIndex(c => c.id === city.value.id)]
}
resetPicker()
function changeColumn({ detail }) {
  const value = Number(detail.value)
  if (Number(detail.column) === 0 && provinces[value]) pickerValue.value = [value, 0]
  if (Number(detail.column) === 1 && pickerCities.value[value]) pickerValue.value = [pickerValue.value[0], value]
}
function changeCity({ detail }) {
  const [province, index] = detail.value
  const next = cityData.filter(c => c.province === provinces[province])[index]
  if (!next) return resetPicker()
  city.value = next
  uni.setStorageSync('baijia_meituan_city', city.value.id)
  resetPicker()
}
function browse(search) {
  uni.navigateTo({ url: '/addon/baijia_tools/pages/catalog/index?city=' + city.value.id + (search ? '&search=1' : '') })
}
onLoad(query => {
  saveInviteReferrer(query?.mid)
  if (query?.search) browse(true)
})
watch(() => systemStore.initStatus, status => {
  if (status !== 'finish' || uni.getStorageSync('baijia_meituan_city') || Number(systemStore.mapConfig.is_open) !== 1) return
  uni.getLocation({
    type: 'gcj02',
    success: async ({ latitude, longitude }) => {
      try {
        const { data } = await getAddressByLatlng({ latlng: latitude + ',' + longitude }, false)
        const match = cityData.find(c => c.name === data?.city && (!data.province || c.province === data.province))
          || cityData.find(c => c.name === data?.province && c.province === c.name)
        if (match && !uni.getStorageSync('baijia_meituan_city')) {
          city.value = match
          uni.setStorageSync('baijia_meituan_city', match.id)
          resetPicker()
        }
      } catch { /* Manual selection remains available. */ }
    }
  })
}, { immediate: true })

onShareAppMessage(() => ({ title: '的确省 · 吃喝玩乐先省一笔', path: '/addon/baijia_tools/pages/home/index' }))
</script>

<style scoped>
@import "../../styles/base.css";
.home { position: relative; min-height: 100vh; background: #fffaf0; color: #202120; font-size: 28rpx; padding-bottom: calc(150rpx + env(safe-area-inset-bottom)); }
.hero { position: absolute; top: 0; left: 0; width: 750rpx; }
.header { position: absolute; top: 0; left: 0; right: 0; z-index: 2; padding-left: 32rpx; padding-right: 32rpx; }
.title-row { display: flex; align-items: center; }
.app-name { font-size: 34rpx; font-weight: 800; }
.search-row { display: flex; align-items: center; gap: 20rpx; margin-top: 12rpx; }
.city { display: flex; align-items: center; }
.city-icon { width: 32rpx; height: 32rpx; }
.city-name { font-size: 28rpx; font-weight: 600; margin: 0 6rpx; max-width: 160rpx; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
.city-arrow { width: 24rpx; height: 24rpx; }
.search { flex: 1; height: 68rpx; border-radius: 34rpx; background: rgba(255, 255, 255, .9); display: flex; align-items: center; padding: 0 24rpx; color: #a6a8a4; font-size: 26rpx; }
.search-input { flex: 1; min-width: 0; height: 68rpx; font-size: 26rpx; color: #202120; }
.search-ph { color: #a6a8a4; }
.search-note { margin-top: 24rpx; font-size: 24rpx; color: #6b6d69; }
.search-icon { width: 32rpx; height: 32rpx; margin-right: 12rpx; }
.body { position: relative; z-index: 1; padding: 0 24rpx; padding-top: 560rpx; } /* hero headline ends ~520rpx */
.card { background: #fff; border-radius: 32rpx; box-shadow: 0 8rpx 28rpx rgba(113, 105, 72, .11); }
.grid { display: flex; flex-wrap: wrap; padding: 24rpx 8rpx 8rpx; }
.grid-item { width: 20%; display: flex; flex-direction: column; align-items: center; padding-bottom: 20rpx; }
.grid-icon { width: 100rpx; height: 100rpx; }
.grid-name { font-size: 24rpx; color: #202120; margin-top: 8rpx; white-space: nowrap; }
.more-grid { width: 100%; display: flex; flex-wrap: wrap; border-top: 1rpx solid #f3eee8; padding-top: 20rpx; }
.more-icon { width: 84rpx; height: 84rpx; margin: 8rpx 0; border-radius: 26rpx; background: #f4fbd2; color: #202120; font-size: 36rpx; font-weight: 800; text-align: center; line-height: 84rpx; }
.entries { display: flex; gap: 20rpx; margin-top: 24rpx; }
.entry { position: relative; flex: 1; height: 180rpx; border-radius: 32rpx; padding: 30rpx 0 0 32rpx; overflow: visible; }
.entry-food { background: linear-gradient(135deg, #e7ffc4, #f9fff0); }
.entry-movie { background: linear-gradient(135deg, #d7fcfd, #f2fffd); }
.entry-title { display: block; font-size: 34rpx; font-weight: 800; }
.entry-sub { display: block; font-size: 20rpx; color: #8a8c88; margin-top: 4rpx; }
.entry-go { display: inline-block; margin-top: 12rpx; padding: 0 16rpx; height: 32rpx; line-height: 32rpx; border-radius: 16rpx; background: #202120; color: #fff; font-size: 20rpx; font-weight: 800; }
.entry-art { position: absolute; right: -8rpx; top: -24rpx; width: 208rpx; height: 208rpx; }
.section-bar { display: flex; justify-content: space-between; align-items: center; margin-top: 40rpx; }
.section-name { display: flex; align-items: center; font-size: 34rpx; font-weight: 800; }
.section-icon { width: 48rpx; height: 48rpx; margin-right: 12rpx; }
.section-more { display: flex; align-items: center; font-size: 24rpx; color: #8a8c88; }
.chevron { width: 24rpx; height: 24rpx; margin-left: 4rpx; }
</style>
