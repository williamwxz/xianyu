<template>
  <view>
    <view class="bj-tabbar">
      <view v-for="tab in TABS" :key="tab.key" class="bj-tab" :class="{ active: tab.key === current, center: tab.center }" @click="go(tab)">
        <image class="bj-tab-icon" :src="`/addon/baijia_tools/static/design/tab/${tab.key}${tab.key === current ? '-active' : ''}.png`" mode="aspectFit" />
        <text v-if="!tab.center" class="bj-tab-text">{{ tab.name }}</text>
      </view>
    </view>
  </view>
</template>

<script setup>
// Floating dark pill nav from the 惠省团 design (Moonvy · Frame 12).
const props = defineProps({ current: { type: String, default: '' } })
const TABS = [
  { key: 'home', name: '首页' },
  { key: 'map', name: '街区', center: true },
  { key: 'mine', name: '我的' }
]
const go = (tab) => {
  if (tab.key === props.current) return
  // #ifdef MP-WEIXIN
  if (typeof wx.vibrateShort === 'function') wx.vibrateShort({ type: 'light', fail: () => {} })
  // #endif
  uni.reLaunch({ url: `/addon/baijia_tools/pages/${tab.key}/index` })
}
</script>

<style scoped>
.bj-tabbar { position: fixed; z-index: 999; left: 50%; transform: translateX(-50%); bottom: calc(24rpx + env(safe-area-inset-bottom)); width: 420rpx; height: 104rpx; padding: 0 20rpx; border-radius: 52rpx; background: #202120; box-shadow: 0 8rpx 28rpx rgba(32, 33, 32, .25); display: flex; align-items: flex-end; }
.bj-tab { flex: 1; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding-bottom: 12rpx; }
.bj-tab-icon { width: 60rpx; height: 60rpx; }
.bj-tab.active .bj-tab-icon { width: 84rpx; height: 84rpx; margin-top: -24rpx; }
.bj-tab-text { font-size: 20rpx; line-height: 1.3; color: #8a8c88; margin-top: 2rpx; }
.bj-tab.center .bj-tab-icon { width: 100rpx; height: 100rpx; margin-top: -40rpx; }
.bj-tab.center.active .bj-tab-icon { width: 88rpx; height: 88rpx; margin-top: 0; }
.bj-tab:active { transform: scale(.96); }
.bj-tab { transition: transform .16s ease-out; }
.bj-tab.active .bj-tab-text { color: #fff; font-weight: 600; }
</style>
