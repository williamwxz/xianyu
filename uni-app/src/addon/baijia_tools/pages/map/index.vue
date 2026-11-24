<template>
  <view class="street">
    <view class="header" :style="{ paddingTop: `${navTop}px` }">
      <view class="title-row" :style="{ height: `${navHeight}px` }">
        <image class="pin" src="/addon/baijia_tools/static/design/icon-location.png" />
        <text class="city">{{ cityName }}</text>
      </view>
      <view class="search" @click="goSearch">
        <image class="search-icon" src="/addon/baijia_tools/static/design/icon-search.png" />
        <text>搜索霸王餐、电影票、美食</text>
      </view>
    </view>

    <!-- Illustration is 375×1148pt in the design; 1pt = 2rpx. -->
    <view class="canvas">
      <image class="street-art" src="/addon/baijia_tools/static/design/map/street.jpg" mode="widthFix" />
      <image
        v-for="spot in SPOTS"
        :key="spot.key"
        class="spot"
        :src="`/addon/baijia_tools/static/design/map/label-${spot.key}.png`"
        :style="{ left: `${spot.x * 2}rpx`, top: `${spot.y * 2}rpx`, width: `${spot.w * 2}rpx`, height: `${spot.h * 2}rpx` }"
        @click="openService(spot.service)"
      />
    </view>
  </view>
  <BjTabbar current="map" />
</template>

<script setup>
import { ref } from 'vue'
import { onShow, onShareAppMessage } from '@dcloudio/uni-app'
import BjTabbar from '../../components/bj-tabbar.vue'
import cityData from '../../lib/meituan-cities.json'
import { openService } from '../../lib/launch.js'

// Shop signs from the 地图 board (Moonvy), position/size in design pt.
const SPOTS = [
  { key: 'movie', service: 'movie', x: 55, y: 330, w: 67, h: 53 },
  { key: 'coffee', service: 'luckin', x: 165, y: 328, w: 64, h: 52 },
  { key: 'tea', service: 'nayuki', x: 265, y: 310, w: 68, h: 58 },
  { key: 'bawangcan', service: 'bawangcan', x: 11, y: 512, w: 70, h: 52 },
  { key: 'food', service: 'daodian', x: 244, y: 509, w: 95, h: 56 },
  { key: 'ai', service: 'ai', x: 187, y: 774, w: 81, h: 49 }
]

const sys = uni.getSystemInfoSync()
const navTop = sys.statusBarHeight || 20
let navHeight = 44
// #ifdef MP-WEIXIN
const capsule = uni.getMenuButtonBoundingClientRect()
if (capsule && capsule.height) { navHeight = capsule.height + (capsule.top - navTop) * 2 }
// #endif

// Same city the home feed uses.
const cityName = ref('')
onShow(() => {
  const id = uni.getStorageSync('baijia_meituan_city') || 'WKV2HMXUEK634WP64CUCUQGM64'
  const city = cityData.find((c) => c.id === id)
  cityName.value = city ? city.name : ''
})

const goSearch = () => uni.reLaunch({ url: '/addon/baijia_tools/pages/home/index?search=1' })

onShareAppMessage(() => ({ title: '的确省 · 逛逛生活街区', path: '/addon/baijia_tools/pages/map/index' }))
</script>

<style scoped>
.street { position: relative; min-height: 100vh; background: #fff9f2; padding-bottom: calc(150rpx + env(safe-area-inset-bottom)); }
.header { position: fixed; top: 0; left: 0; right: 0; z-index: 2; padding: 0 32rpx 24rpx; background: linear-gradient(180deg, rgba(255, 249, 242, .95) 70%, rgba(255, 249, 242, 0)); }
.title-row { display: flex; align-items: center; }
.pin { width: 32rpx; height: 32rpx; }
.city { font-size: 28rpx; font-weight: 700; color: #202120; margin-left: 6rpx; }
.search { display: flex; align-items: center; height: 68rpx; margin-top: 12rpx; padding: 0 24rpx; border-radius: 34rpx; background: rgba(255, 255, 255, .92); color: #a6a8a4; font-size: 26rpx; box-shadow: 0 4rpx 16rpx rgba(113, 105, 72, .12); }
.search-icon { width: 32rpx; height: 32rpx; margin-right: 12rpx; }
.canvas { position: relative; width: 750rpx; }
.street-art { display: block; width: 750rpx; }
.spot { position: absolute; z-index: 1; }
.spot:active { transform: scale(.94); }
</style>
