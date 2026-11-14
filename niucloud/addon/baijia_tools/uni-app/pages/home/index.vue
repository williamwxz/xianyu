<template>
  <view class="home">
    <view class="top">
      <view class="brand">省</view>
      <view class="search" @click="goSearch"><text class="search-icon">⌕</text><text class="search-text">搜索霸王餐、电影票、团购</text></view>
    </view>

    <view class="hero">
      <text class="hero-kicker">百迦好省 · 生活省钱入口</text>
      <text class="hero-title">吃喝玩乐，先省一笔</text>
      <text class="hero-sub">霸王餐 · 电影票 · 到店团购 · 加油洗车</text>
    </view>

    <view class="grid white-card">
      <button v-for="item in grid" :key="item.id" class="grid-item" @click="openService(item.id)">
        <view class="grid-icon" :style="gradient(item)">{{ item.icon }}</view>
        <text class="grid-name">{{ item.name }}</text>
      </button>
    </view>

    <view class="brands white-card">
      <view class="section-bar"><text class="section-name">大牌点餐</text><text class="brand-note">在线下单 · 到店自取</text></view>
      <view class="brand-grid">
        <button v-for="item in brands" :key="item.id" class="brand-item" @click="openService(item.id)">
          <view class="brand-logo-wrap">
            <image v-if="acts[item.jtk] && acts[item.jtk].icon" class="brand-logo" :src="acts[item.jtk].icon" mode="aspectFill" />
            <view v-else class="brand-logo brand-fallback" :style="gradient(item)">{{ item.icon }}</view>
            <text v-if="item.tag" class="brand-tag">{{ item.tag }}</text>
          </view>
          <text class="brand-name">{{ item.name }}</text>
        </button>
      </view>
    </view>

    <view class="cube">
      <button class="cube-big" :style="gradient(bawangcan)" @click="openService('bawangcan')">
        <text class="cube-title">霸王餐</text>
        <text class="cube-sub">美食返现 · 吃好不花钱</text>
        <view class="cube-tag">免费吃</view>
        <text class="cube-art">餐</text>
      </button>
      <view class="cube-side">
        <button class="cube-small" :style="gradient(carwash)" @click="openService('carwash')">
          <text class="cube-title small">洗车服务</text><text class="cube-sub">洁净焕新 一路畅行</text><text class="cube-art small">洗</text>
        </button>
        <button class="cube-small" :style="gradient(fuel)" @click="openService('fuel')">
          <text class="cube-title small">加油服务</text><text class="cube-sub">方便快捷 能量满满</text><text class="cube-art small">油</text>
        </button>
      </view>
    </view>

    <view class="section white-card">
      <view class="section-bar"><text class="section-name">热门电影</text><button class="more" @click="openService('movie')">查看更多 ›</button></view>
      <view class="empty" @click="openService('movie')">
        <view class="empty-art" :style="gradient(movie)">影</view>
        <view class="empty-copy"><text class="empty-title">特价影票 · 在线选座</text><text class="empty-text">全国影院热映影片，点击进入查看排片</text></view>
      </view>
    </view>

    <view class="section white-card">
      <view class="section-bar"><text class="section-name">好物优惠</text><button class="more" @click="openService('goods')">查看更多 ›</button></view>
      <view class="empty" @click="openService('goods')">
        <view class="empty-art" :style="gradient(goods)">券</view>
        <view class="empty-copy"><text class="empty-title">领券中心 · 大牌好物</text><text class="empty-text">先领券再下单，到手价更低</text></view>
      </view>
    </view>

    <view class="tools white-card">
      <button class="tool" @click="go('/addon/baijia_tools/pages/calculator/index')"><text class="tool-name">优惠计算</text><text class="tool-sub">折扣 · 满减 · 券后价</text></button>
      <button class="tool" @click="go('/addon/baijia_tools/pages/guide/index')"><text class="tool-name">省钱指南</text><text class="tool-sub">凑单 · 叠券 · 折扣速查</text></button>
    </view>
  </view>
  <tabbar />
</template>

<script setup>
import { computed, ref } from 'vue'
import { onLoad, onShareAppMessage } from '@dcloudio/uni-app'
import { HOME_GRID, BRANDS, getService } from '../../lib/services.mjs'
import { loadActs, openService } from '../../lib/launch.js'

const grid = computed(() => HOME_GRID.map(getService))
const brands = BRANDS.map(getService)
const acts = ref({})
onLoad(() => { loadActs().then((data) => { acts.value = data }) })
const bawangcan = getService('bawangcan')
const carwash = getService('carwash')
const fuel = getService('fuel')
const movie = getService('movie')
const goods = getService('goods')

const gradient = (item) => ({ background: `linear-gradient(135deg, ${item.color}, ${item.colorTo})` })
const go = (url) => uni.navigateTo({ url })
const goSearch = () => uni.navigateTo({ url: '/addon/baijia_tools/pages/groupbuy/index?focus=1' })

onShareAppMessage(() => ({ title: '百迦好省 · 吃喝玩乐先省一笔', path: '/addon/baijia_tools/pages/home/index' }))
</script>

<style scoped>
@import "../../styles/base.css";
.home { min-height: 100vh; background: #f5f6f8; padding: 20rpx 24rpx calc(150rpx + env(safe-area-inset-bottom)); color: #303238; font-size: 28rpx; }
.top { display: flex; align-items: center; gap: 18rpx; }
.brand { width: 68rpx; height: 68rpx; border-radius: 16rpx; background: #ff4c30; color: #fff; font-size: 38rpx; font-weight: 700; text-align: center; line-height: 68rpx; flex-shrink: 0; }
.search { flex: 1; height: 68rpx; border-radius: 34rpx; background: #fff; display: flex; align-items: center; padding: 0 26rpx; color: #a0a3ab; font-size: 26rpx; }
.search-icon { font-size: 34rpx; margin-right: 12rpx; }
.hero { margin-top: 22rpx; padding: 36rpx 32rpx; border-radius: 24rpx; background: linear-gradient(120deg, #ff4c30, #ff9a3c); color: #fff; }
.hero text { display: block; }
.hero-kicker { font-size: 22rpx; opacity: .9; }
.hero-title { font-size: 44rpx; font-weight: 800; margin: 12rpx 0; }
.hero-sub { font-size: 24rpx; color: #fff4dc; }
.grid { display: flex; flex-wrap: wrap; margin-top: 22rpx; padding: 18rpx 0 6rpx; }
.grid-item { width: 25%; background: transparent; padding: 10rpx 0 18rpx; line-height: 1.4; display: flex; flex-direction: column; align-items: center; }
.grid-icon { width: 84rpx; height: 84rpx; border-radius: 26rpx; color: #fff; font-size: 38rpx; font-weight: 700; line-height: 84rpx; text-align: center; }
.grid-name { font-size: 24rpx; color: #43464d; margin-top: 10rpx; }
.brands { margin-top: 22rpx; padding: 26rpx 12rpx 8rpx; }
.brands .section-bar { padding: 0 12rpx; }
.brand-note { font-size: 23rpx; color: #aaa; }
.brand-grid { display: flex; flex-wrap: wrap; margin-top: 12rpx; }
.brand-item { width: 20%; background: transparent; padding: 14rpx 0 16rpx; line-height: 1.4; display: flex; flex-direction: column; align-items: center; }
.brand-logo-wrap { position: relative; }
.brand-logo { width: 92rpx; height: 92rpx; border-radius: 50%; display: block; }
.brand-fallback { color: #fff; font-size: 38rpx; font-weight: 800; text-align: center; line-height: 92rpx; }
.brand-tag { position: absolute; top: -12rpx; right: -26rpx; padding: 2rpx 10rpx; border-radius: 16rpx 16rpx 16rpx 0; background: #ff4c30; color: #fff; font-size: 18rpx; white-space: nowrap; }
.brand-name { font-size: 23rpx; color: #43464d; margin-top: 10rpx; }
.cube { display: flex; gap: 16rpx; margin-top: 22rpx; height: 420rpx; }
.cube-big, .cube-small { position: relative; overflow: hidden; border-radius: 22rpx; color: #fff; text-align: left; padding: 28rpx 24rpx; line-height: 1.4; }
.cube-big { flex: 1; }
.cube-side { flex: 1.25; display: flex; flex-direction: column; gap: 16rpx; }
.cube-small { flex: 1; }
.cube-title { display: block; font-size: 46rpx; font-weight: 800; letter-spacing: 2rpx; }
.cube-title.small { font-size: 36rpx; }
.cube-sub { display: block; font-size: 22rpx; opacity: .92; margin-top: 6rpx; }
.cube-tag { display: inline-block; margin-top: 22rpx; padding: 6rpx 22rpx; border-radius: 30rpx; background: #fff; color: #ff5a2c; font-size: 24rpx; font-weight: 700; }
.cube-art { position: absolute; right: -10rpx; bottom: -30rpx; font-size: 200rpx; font-weight: 900; opacity: .18; }
.cube-art.small { font-size: 130rpx; bottom: -24rpx; }
.section { margin-top: 22rpx; padding: 26rpx 24rpx; }
.section-bar { display: flex; justify-content: space-between; align-items: center; }
.section-name { font-size: 32rpx; font-weight: 700; }
.more { background: transparent; padding: 0; font-size: 24rpx; color: #999; line-height: 1.5; }
.empty { display: flex; align-items: center; margin-top: 22rpx; padding: 22rpx; border-radius: 18rpx; background: #f8f9fb; }
.empty-art { width: 96rpx; height: 128rpx; border-radius: 14rpx; color: #fff; font-size: 48rpx; font-weight: 800; text-align: center; line-height: 128rpx; flex-shrink: 0; }
.empty-copy { padding-left: 22rpx; }
.empty-title { display: block; font-size: 27rpx; font-weight: 600; color: #3a3d44; }
.empty-text { display: block; font-size: 23rpx; color: #9a9da5; margin-top: 8rpx; }
.tools { display: flex; margin-top: 22rpx; padding: 10rpx; }
.tool { flex: 1; background: transparent; text-align: left; padding: 20rpx; line-height: 1.5; }
.tool + .tool { border-left: 1rpx solid #f0f0f2; }
.tool-name { display: block; font-size: 28rpx; font-weight: 700; }
.tool-sub { display: block; font-size: 22rpx; color: #9a9da5; }
</style>
