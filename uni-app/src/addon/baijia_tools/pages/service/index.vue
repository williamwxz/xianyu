<template>
  <web-view v-if="webviewUrl" :src="webviewUrl" />
  <view v-else class="page-wrap">
    <view v-if="service" class="hero" :style="{ background: `linear-gradient(135deg, ${service.color}, ${service.colorTo})` }">
      <text class="hero-art">{{ service.icon }}</text>
      <text class="hero-name">{{ service.name }}</text>
      <text class="hero-sub">{{ service.slogan }}</text>
    </view>
    <view v-if="service" class="card white-card">
      <view class="status"><view class="dot" /><text>即将上线</text></view>
      <text class="lead">{{ service.name }}正在接入合作渠道，开通后可以在这里直接使用。</text>
      <view class="row"><text class="row-label">接入渠道</text><text class="row-value">{{ service.channel }}</text></view>
      <view class="row"><text class="row-label">当前状态</text><text class="row-value">待开通</text></view>
    </view>
    <view v-if="!service" class="card white-card"><text class="lead">没有找到这个服务。</text></view>
    <view class="actions">
      <button class="primary" @click="goHome">去首页看看</button>
      <button class="ghost" @click="goCalculator">先用优惠计算</button>
    </view>
  </view>
</template>

<script setup>
import { ref, computed } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getService, isReady } from '../../lib/services.mjs'

const service = ref(null)
onLoad((query) => {
  service.value = getService(query && query.id)
  if (service.value) uni.setNavigationBarTitle({ title: service.value.name })
})
const webviewUrl = computed(() => {
  const item = service.value
  return item && isReady(item) && item.launch.type === 'webview' ? item.launch.url : ''
})
const goHome = () => uni.reLaunch({ url: '/addon/baijia_tools/pages/home/index' })
const goCalculator = () => uni.navigateTo({ url: '/addon/baijia_tools/pages/calculator/index' })
</script>

<style scoped>
@import "../../styles/base.css";
.hero { position: relative; overflow: hidden; border-radius: 24rpx; padding: 48rpx 36rpx; color: #fff; }
.hero text { display: block; }
.hero-art { position: absolute; right: 10rpx; bottom: -40rpx; font-size: 220rpx; font-weight: 900; opacity: .18; }
.hero-name { font-size: 48rpx; font-weight: 800; }
.hero-sub { font-size: 26rpx; margin-top: 10rpx; opacity: .92; }
.card { margin-top: 24rpx; padding: 32rpx; }
.status { display: flex; align-items: center; font-size: 26rpx; color: #ff6a2c; font-weight: 600; }
.dot { width: 14rpx; height: 14rpx; border-radius: 50%; background: #ff6a2c; margin-right: 12rpx; }
.lead { display: block; font-size: 28rpx; color: #43464d; line-height: 1.8; margin: 18rpx 0 10rpx; }
.row { display: flex; justify-content: space-between; padding: 20rpx 0; border-top: 1rpx solid #f1f1f3; font-size: 25rpx; }
.row-label { color: #999; flex-shrink: 0; margin-right: 24rpx; }
.row-value { color: #43464d; text-align: right; }
.actions { margin-top: 36rpx; }
.primary, .ghost { height: 88rpx; line-height: 88rpx; border-radius: 44rpx; font-size: 28rpx; }
.primary { background: linear-gradient(90deg, #ff4c30, #ff8a3c); color: #fff; }
.ghost { margin-top: 20rpx; background: #fff; color: #ff5a2c; }
</style>
