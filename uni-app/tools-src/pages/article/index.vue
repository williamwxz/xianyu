<template><view class="page-wrap"><view v-if="article" class="article white-card"><text class="category">{{ article.tag }}</text><text class="article-title">{{ article.title }}</text><view class="line" /><text v-for="(paragraph, index) in article.paragraphs" :key="index" class="paragraph">{{ paragraph }}</text><button class="try" @click="openCalculator({mode:article.mode,example:true})">打开计算器试一试 ›</button></view><view v-else class="missing">没有找到这篇内容<button @click="goGuides">返回省钱攻略</button></view><text class="article-note">本文为计算规则说明，活动优惠以商家实际结算为准。</text></view></template>
<script setup>
import { ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { ARTICLES, openCalculator } from '../../lib/content.mjs'
const article = ref(null)
onLoad(options => { article.value = ARTICLES.find(item => item.id === options.id) || null })
function goGuides() { uni.switchTab({url:'/pages/guide/index'}) }
</script>
<style scoped>
.article { padding: 36rpx 30rpx; }
.category { font-size: 23rpx; color: #ff623a; background: #fff0e9; border-radius: 8rpx; padding: 8rpx 14rpx; }
.article-title { display: block; font-size: 40rpx; font-weight: 700; line-height: 1.5; margin: 28rpx 0; }
.line { height: 1rpx; background: #eee; margin-bottom: 30rpx; }
.paragraph { display: block; font-size: 29rpx; color: #686868; line-height: 1.9; margin-bottom: 28rpx; }
.try { margin-top: 40rpx; background: linear-gradient(100deg,#ff6b2b,#ff393f); color: white; border-radius: 40rpx; padding: 22rpx; font-size: 27rpx; line-height: 1.5; }
.article-note { display: block; padding: 24rpx 12rpx; font-size: 23rpx; color: #aaa; line-height: 1.8; }
.missing { padding: 32rpx; text-align: center; }
</style>
