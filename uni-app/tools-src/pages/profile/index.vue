<template><view class="page-wrap"><view class="profile-hero"><view class="avatar">省</view><view><text class="profile-name">我的省钱小本</text><text class="profile-sub">不用登录，记录留在本机</text></view></view><view class="profile-stats white-card"><view><text>{{ history.length }}</text><text>已保存计算</text></view><view><text>20</text><text>最多保存条数</text></view><view><text>本机</text><text>记录保存位置</text></view></view><view class="section-head"><text class="heading">我的计算记录</text><button @click="openCalculator()">去计算 ›</button></view><view v-if="!history.length" class="profile-empty white-card"><text class="empty-symbol">记</text><text>还没有保存的记录</text><text class="profile-sub">算完后点“保存本次计算”，就能在这里找到。</text><button @click="openCalculator()">开始第一笔计算</button></view><button v-for="item in history" :key="item.id" class="record white-card" @click="openCalculator({record:item})"><view><text class="record-title">{{ item.label }}</text><text class="record-detail">{{ item.summary }}</text><text class="record-date">{{ item.date }}</text></view><view class="record-price"><text>¥{{ item.finalText }}</text><text>查看计算 ›</text></view></button><button class="profile-help white-card" @click="openAbout"><text>使用说明与隐私</text><text>›</text></button><text class="profile-version">百迦好省 · 1.0.1</text></view></template>
<script setup>
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { validHistory, HISTORY_KEY } from '../../lib/calculator.mjs'
import { openCalculator } from '../../lib/content.mjs'
const history = ref([])
onShow(() => { try { history.value=validHistory(uni.getStorageSync(HISTORY_KEY)) } catch { uni.showToast({title:'本地记录暂时无法读取',icon:'none'}) } })
function openAbout() { uni.navigateTo({url:'/pages/about/index'}) }
</script>
<style scoped>
.profile-hero { display: flex; align-items: center; gap: 24rpx; padding: 30rpx 18rpx 40rpx; }
.avatar { width: 110rpx; height: 110rpx; color: white; font-size: 66rpx; font-weight: 700; line-height: 110rpx; text-align: center; border-radius: 30rpx; background: linear-gradient(145deg,#ff8144,#ff3142); }
.profile-name { display: block; font-size: 37rpx; font-weight: 700; }
.profile-sub { display: block; font-size: 23rpx; color: #999; margin-top: 12rpx; line-height: 1.8; }
.profile-stats { display: flex; padding: 32rpx 10rpx; }
.profile-stats > view { flex: 1; text-align: center; }
.profile-stats text { display: block; font-size: 40rpx; font-weight: 700; color: #ff6037; }
.profile-stats text + text { font-size: 21rpx; font-weight: 400; color: #999; margin-top: 10rpx; }
.profile-empty { padding: 36rpx 24rpx; text-align: center; font-size: 28rpx; }
.empty-symbol { display: block; color: #ffd5c2; font-size: 70rpx; margin-bottom: 15rpx; }
.profile-empty button { background: #fff1e9; color: #ff6a3b; font-size: 25rpx; border-radius: 30rpx; padding: 15rpx 28rpx; line-height: 1.5; display: inline-block; margin-top: 26rpx; }
.record { display: flex; align-items: center; justify-content: space-between; padding: 26rpx; margin-bottom: 16rpx; text-align: left; line-height: 1.6; }
.record-title { display: block; font-size: 28rpx; font-weight: 600; color: #444; }
.record-detail { display: block; font-size: 23rpx; color: #999; }
.record-date { display: block; font-size: 20rpx; color: #bbb; margin-top: 7rpx; }
.record-price { text-align: right; color: #ff5938; font-size: 34rpx; font-weight: 600; }
.record-price text { display: block; }
.record-price text + text { font-size: 21rpx; font-weight: 400; color: #aaa; margin-top: 8rpx; }
.profile-help { margin-top: 28rpx; display: flex; justify-content: space-between; padding: 27rpx; line-height: 1.5; color: #555; font-size: 27rpx; }
.profile-version { display: block; text-align: center; color: #bbb; font-size: 22rpx; margin: 35rpx 0; }
</style>
