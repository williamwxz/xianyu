<template>
  <view class="page-wrap">
    <view class="banner">
      <text class="banner-title">聚合快递</text>
      <text class="banner-sub">多家快递比价 · 上门取件 · 物流查询</text>
    </view>

    <view class="send white-card">
      <view class="addr" @click="goAddress"><view class="tag send-tag">寄</view><view class="addr-copy"><text class="addr-title">寄件人信息</text><text class="addr-sub">从地址簿选择</text></view><text class="arrow">›</text></view>
      <view class="addr" @click="goAddress"><view class="tag recv-tag">收</view><view class="addr-copy"><text class="addr-title">收件人信息</text><text class="addr-sub">从地址簿选择</text></view><text class="arrow">›</text></view>
      <button class="primary" @click="openService('express')">特价寄快递</button>
      <text class="note">特价寄件由合作快递平台提供，下单、取件和售后均在对方页面完成。</text>
    </view>

    <view class="track white-card">
      <text class="track-title">查快递</text>
      <view class="track-row">
        <input v-model="number" class="track-input" maxlength="40" placeholder="输入快递单号" placeholder-class="placeholder" confirm-type="search" @confirm="track" />
        <button class="track-btn" @click="track">查询</button>
      </view>
      <text v-if="message" class="note">{{ message }}</text>
    </view>

    <view class="tips white-card">
      <text class="tips-title">寄件小贴士</text>
      <text class="tip">· 贵重物品建议保价，并保留开箱照片。</text>
      <text class="tip">· 液体、电池等物品请先确认快递公司的收寄规则。</text>
      <text class="tip">· 包装尺寸会影响计费重量，尽量选择合适的箱子。</text>
    </view>
  </view>
  <tabbar />
</template>

<script setup>
import { ref } from 'vue'
import { openService } from '../../lib/launch.js'

const number = ref('')
const message = ref('')
const goAddress = () => uni.navigateTo({ url: '/app/pages/member/address' })
const track = () => {
  const value = number.value.trim()
  if (!/^[A-Za-z0-9-]{6,40}$/.test(value)) { message.value = '请输入正确的快递单号（6 位以上字母或数字）。'; return }
  message.value = '物流查询即将开通，届时可在这里直接查看轨迹。'
}
</script>

<style scoped>
@import "../../styles/base.css";
.banner { border-radius: 24rpx; padding: 40rpx 32rpx; background: linear-gradient(120deg, #6b4ff0, #a58bff); color: #fff; }
.banner text { display: block; }
.banner-title { font-size: 44rpx; font-weight: 800; }
.banner-sub { font-size: 24rpx; margin-top: 10rpx; opacity: .92; }
.send, .track, .tips { margin-top: 22rpx; padding: 26rpx 24rpx; }
.addr { display: flex; align-items: center; padding: 22rpx 0; border-bottom: 1rpx solid #f1f1f3; }
.tag { width: 60rpx; height: 60rpx; border-radius: 50%; color: #fff; font-weight: 700; text-align: center; line-height: 60rpx; font-size: 28rpx; flex-shrink: 0; }
.send-tag { background: #2f7cf6; }
.recv-tag { background: #ff6a2c; }
.addr-copy { flex: 1; padding-left: 20rpx; }
.addr-title { display: block; font-size: 29rpx; font-weight: 600; }
.addr-sub { display: block; font-size: 23rpx; color: #999; margin-top: 4rpx; }
.arrow { font-size: 36rpx; color: #bbb; }
.primary { margin-top: 28rpx; height: 88rpx; line-height: 88rpx; border-radius: 44rpx; background: linear-gradient(90deg, #6b4ff0, #a58bff); color: #fff; font-size: 29rpx; font-weight: 600; }
.note { display: block; font-size: 23rpx; color: #999; line-height: 1.7; margin-top: 16rpx; }
.track-title, .tips-title { display: block; font-size: 31rpx; font-weight: 700; }
.track-row { display: flex; gap: 16rpx; margin-top: 18rpx; }
.track-input { flex: 1; height: 76rpx; border-radius: 38rpx; background: #f4f5f7; padding: 0 28rpx; font-size: 26rpx; }
.placeholder { color: #a8abb2; }
.track-btn { width: 140rpx; height: 76rpx; line-height: 76rpx; border-radius: 38rpx; background: #6b4ff0; color: #fff; font-size: 27rpx; }
.tip { display: block; font-size: 25rpx; color: #666; line-height: 1.9; margin-top: 8rpx; }
</style>
