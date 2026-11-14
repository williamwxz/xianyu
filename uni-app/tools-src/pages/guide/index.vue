<template><view class="page-wrap"><view class="guide-banner"><text>买前多看一步</text><text>省钱，先从看懂优惠开始</text></view><button v-for="item in ARTICLES" :key="item.id" class="guide-row white-card" @click="openArticle(item.id)"><view class="guide-art" :style="{ background: item.color }">{{ item.symbol }}</view><view class="guide-copy"><text class="guide-tag">{{ item.tag }}</text><text class="guide-title">{{ item.title }}</text><text class="guide-brief">{{ item.brief }}</text></view><text class="guide-arrow">›</text></button><view class="guide-note">结合实际规则计算，让每一笔优惠都清清楚楚。</view></view></template>
<script setup>
import { ARTICLES, openArticle } from '../../lib/content.mjs'
</script>
<style scoped>
.guide-banner { padding: 32rpx; border-radius: 24rpx; background: linear-gradient(110deg,#ff4d34,#ff942f); color: white; margin-bottom: 24rpx; }
.guide-banner text { display: block; font-size: 38rpx; font-weight: 700; }
.guide-banner text + text { font-size: 24rpx; font-weight: 400; margin-top: 12rpx; color: #fff2d9; }
.guide-row { display: flex; align-items: center; padding: 26rpx; margin-bottom: 20rpx; text-align: left; line-height: 1.5; }
.guide-art { flex-shrink: 0; width: 106rpx; height: 120rpx; color: white; border-radius: 18rpx; font-size: 66rpx; font-weight: 700; text-align: center; line-height: 120rpx; }
.guide-copy { flex: 1; min-width: 0; padding: 0 22rpx; }
.guide-tag { font-size: 20rpx; color: #fa663e; }
.guide-title { display: block; font-size: 29rpx; font-weight: 600; color: #353535; margin: 7rpx 0; }
.guide-brief { display: block; font-size: 23rpx; color: #999; }
.guide-arrow { font-size: 36rpx; color: #bbb; }
.guide-note { font-size: 23rpx; color: #aaa; text-align: center; padding: 24rpx 10rpx; line-height: 1.8; }
</style>
