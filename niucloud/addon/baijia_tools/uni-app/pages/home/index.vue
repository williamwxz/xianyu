<template>
  <view class="home">
    <image class="hero" src="/addon/baijia_tools/static/design/hero.png" mode="widthFix" />
    <view class="header" :style="{ paddingTop: `${navTop}px` }">
      <view class="title-row" :style="{ height: `${navHeight}px` }"><text class="app-name">百迦好省</text></view>
      <view class="search-row">
        <picker :range="cities" range-key="label" :value="cityIndex" @change="changeCity">
          <view class="city">
            <image class="city-icon" src="/addon/baijia_tools/static/design/icon-location.png" />
            <text class="city-name">{{ city.name }}</text>
            <image class="city-arrow" src="/addon/baijia_tools/static/design/icon-arrow-down.png" />
          </view>
        </picker>
        <view class="search">
          <image class="search-icon" src="/addon/baijia_tools/static/design/icon-search.png" />
          <input v-model="keyword" class="search-input" :focus="searchFocus" maxlength="100" confirm-type="search" placeholder="搜索美团优惠商品" placeholder-class="search-ph" @confirm="search" />
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
        <view v-if="activeKeyword" class="section-more" @click="clearSearch"><text>清除搜索</text></view>
      </view>
      <view v-if="activeKeyword" class="search-note">「{{ activeKeyword }}」的搜索结果</view>
      <scroll-view v-else scroll-x class="tabs" :show-scrollbar="false">
        <view v-for="t in TOPICS" :key="t.topic" class="tab" :class="{ active: t.topic === topic }" @click="switchTopic(t.topic)">{{ t.name }}</view>
      </scroll-view>

      <view class="products">
        <view v-for="item in products" :key="item.id" class="product" @click="openMeituanProduct(item)">
          <image v-if="item.image" class="photo" :src="item.image" mode="aspectFill" />
          <view v-else class="photo photo-empty">团购</view>
          <view class="product-info">
            <text class="product-name">{{ item.name }}</text>
            <text v-if="item.sales" class="product-meta">已售 {{ item.sales }}</text>
            <view class="prices">
              <text class="price"><text class="yen">¥</text>{{ item.price }}</text>
              <text v-if="Number(item.original_price) > Number(item.price)" class="original">¥{{ item.original_price }}</text>
            </view>
          </view>
        </view>
      </view>
      <text v-if="loading" class="hint">正在加载优惠…</text>
      <view v-else-if="error" class="hint" @click="loadProducts">{{ error }} · 点击重试</view>
      <text v-else-if="!products.length" class="hint">{{ activeKeyword ? '没有找到相关商品，换个关键词试试' : '当前城市暂无该榜单商品' }}</text>
    </view>
  </view>
  <BjTabbar current="home" />
</template>

<script setup>
import { computed, ref } from 'vue'
import { onLoad, onShareAppMessage } from '@dcloudio/uni-app'
import request from '@/utils/request'
import BjTabbar from '../../components/bj-tabbar.vue'
import cityData from '../../lib/meituan-cities.json'
import { HOME_GRID, MORE_GRID, getService } from '../../lib/services.mjs'
import { openService, openMeituanProduct } from '../../lib/launch.js'

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

const cities = cityData.map((c) => ({ ...c, label: `${c.province} · ${c.name}` }))
const savedCity = uni.getStorageSync('baijia_meituan_city') || 'WKV2HMXUEK634WP64CUCUQGM64'
const cityIndex = ref(Math.max(0, cities.findIndex((c) => c.id === savedCity)))
const city = computed(() => cities[cityIndex.value])

// Server allows topics 2 / 3 / 5 (see MEITUAN.md).
const TOPICS = [{ topic: 2, name: '今日必推' }, { topic: 3, name: '同城优选' }, { topic: 5, name: '实时热销' }]
const topic = ref(2)
const products = ref([])
// Keyword search replaces the topic feed until cleared (the server's keyword search covers all Meituan categories).
const keyword = ref('')
const activeKeyword = ref('')
const searchFocus = ref(false)
const loading = ref(false)
const error = ref('')
let version = 0
async function loadProducts() {
  const v = ++version
  loading.value = true; error.value = ''; products.value = []
  try {
    const params = activeKeyword.value ? { city_id: city.value.id, keyword: activeKeyword.value } : { city_id: city.value.id, topic: topic.value }
    const res = await request.get('baijia_tools/meituan/products', params, { showErrorMessage: false })
    if (v === version) products.value = res.data.items || []
  } catch (e) { if (v === version) error.value = '暂时无法加载' }
  finally { if (v === version) loading.value = false }
}
const switchTopic = (t) => { if (t !== topic.value) { topic.value = t; loadProducts() } }
const changeCity = (e) => {
  cityIndex.value = Number(e.detail.value)
  uni.setStorageSync('baijia_meituan_city', city.value.id)
  loadProducts()
}

const search = () => {
  const q = keyword.value.trim()
  if (!q) { clearSearch(); return }
  activeKeyword.value = q
  loadProducts()
}
const clearSearch = () => {
  keyword.value = ''
  if (!activeKeyword.value) return
  activeKeyword.value = ''
  loadProducts()
}

// ?search=1 comes from the search box on the 街区 page.
onLoad((query) => {
  searchFocus.value = Boolean(query && query.search)
  loadProducts()
})

onShareAppMessage(() => ({ title: '百迦好省 · 吃喝玩乐先省一笔', path: '/addon/baijia_tools/pages/home/index' }))
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
.tabs { white-space: nowrap; margin-top: 24rpx; }
.tab { display: inline-block; height: 60rpx; line-height: 60rpx; padding: 0 28rpx; margin-right: 16rpx; border-radius: 30rpx; background: #fff; color: #43464d; font-size: 26rpx; }
.tab.active { background: #d4fa20; color: #202120; font-weight: 800; }
.products { display: flex; flex-wrap: wrap; justify-content: space-between; margin-top: 20rpx; }
.product { width: 340rpx; margin-bottom: 20rpx; border-radius: 24rpx; background: #fff; overflow: hidden; }
.photo { display: block; width: 340rpx; height: 240rpx; background: #f3eee8; }
.photo-empty { color: #aaa; text-align: center; line-height: 240rpx; }
.product-info { padding: 16rpx 18rpx 20rpx; }
.product-name { display: block; font-size: 27rpx; font-weight: 700; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
.product-meta { display: block; font-size: 22rpx; color: #8a8c88; margin-top: 6rpx; }
.prices { margin-top: 8rpx; }
.price { font-size: 38rpx; font-weight: 800; color: #f5222d; }
.yen { font-size: 24rpx; }
.original { font-size: 22rpx; color: #b9bbb7; text-decoration: line-through; margin-left: 10rpx; }
.hint { display: block; text-align: center; font-size: 24rpx; color: #8a8c88; padding: 24rpx 0; }
</style>
