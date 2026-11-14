<template>
  <view class="home">
    <view class="search-row"><view class="brand">省</view><view class="search-box"><view class="search-icon" /><input v-model="query" placeholder="搜索工具、折扣、满减" aria-label="搜索省钱工具" confirm-type="search" /><button v-if="query" class="clear-search" @click="query = ''">清除</button></view></view>

    <view v-if="!query" class="hero" @click="openCalculator({ mode: 'discount' })">
      <view class="hero-circle one" /><view class="hero-circle two" />
      <view class="hero-copy"><text class="hero-kicker">百迦好省 · 日常省钱好帮手</text><text class="hero-title">会算优惠<text>才会省</text></text><text class="hero-sub">折扣 · 满减 · 用券，一次算清</text><view class="hero-cta">马上算一算 <text>›</text></view></view>
      <view class="hero-art"><view class="ticket"><text>优惠计算</text><text class="big-percent">%</text><text class="ticket-bottom">算清到手价</text></view><view class="coin">¥</view><view class="little-ticket">省</view></view>
    </view>

    <view class="entry-panel white-card" :class="{ searching: query }">
      <view v-if="query" class="search-title">找到 {{ filteredEntries.length }} 个工具与指南</view>
      <view class="entry-grid"><button v-for="item in filteredEntries" :key="item.id" class="entry" @click="openEntry(item)"><view class="entry-icon" :style="{ background: item.color }"><text>{{ item.icon }}</text><text v-if="item.badge" class="entry-badge">{{ item.badge }}</text></view><text class="entry-name">{{ item.name }}</text></button></view>
      <view v-if="!filteredEntries.length" class="no-results">没有找到相关内容，试试“满减”或“优惠券”。</view>
    </view>

    <template v-if="!query">
      <view class="benefits"><text>✓ 本地计算</text><text>✓ 无需登录</text><text>✓ 随手比较</text></view>
      <view class="section-head"><text class="heading">省钱小课堂</text><button @click="openGuides">更多 ›</button></view>
      <view class="articles"><button v-for="item in ARTICLES" :key="item.id" class="article-card" @click="openArticle(item.id)"><view class="article-cover" :style="{ background: item.color }"><text class="article-tag">{{ item.tag }}</text><text class="cover-symbol">{{ item.symbol }}</text><text class="cover-sub">买前算一算</text></view><view class="article-copy"><text>{{ item.title }}</text><text class="read">查看攻略 ›</text></view></button></view>
      <view class="section-head"><text class="heading">常用优惠，马上试算</text></view>
      <button class="scenario white-card" @click="openCalculator({mode:'discount', example:true})"><view class="scenario-icon">8.5<text>折</text></view><view class="scenario-copy"><text>199 元打 8.5 折，要付多少？</text><text class="scenario-desc">看看到手价，也看看省了多少</text></view><text class="scenario-arrow">›</text></button>
      <button class="scenario white-card" @click="openCalculator({mode:'reduction', example:true})"><view class="scenario-icon orange">满<text>200−30</text></view><view class="scenario-copy"><text>满减一次和每满减，差在哪？</text><text class="scenario-desc">同样的金额，比较两种优惠规则</text></view><text class="scenario-arrow">›</text></button>
      <view class="home-note">先算清价格，再决定怎么买。</view>
    </template>
  </view>
</template>
<script setup>
import { ref, computed } from 'vue'
import { ENTRIES, ARTICLES, openEntry, openCalculator, openArticle } from '../../lib/content.mjs'
const query = ref('')
const filteredEntries = computed(() => { const term=query.value.trim(); return !term ? ENTRIES : ENTRIES.filter(item => `${item.name} ${item.mode === 'coupon' ? '优惠券 到手价' : ''}`.includes(term)) })
function openGuides() { uni.switchTab({url:'/pages/guide/index'}) }
</script>
<style>
.home { max-width: 960rpx; padding: 0 16rpx 32rpx; margin: 0 auto; }
.search-row { display: flex; align-items: center; gap: 18rpx; padding: 16rpx 8rpx 24rpx; background: #fff; margin: 0 -16rpx; padding-left: 24rpx; padding-right: 24rpx; }
.brand { width: 68rpx; height: 68rpx; border-radius: 15rpx; background: linear-gradient(150deg,#ff593d,#f51425); color: #fff; font-size: 46rpx; line-height: 68rpx; font-weight: 700; text-align: center; }
.search-box { flex: 1; min-width: 0; height: 68rpx; background: #f7f8fa; border: 1rpx solid #ebebed; border-radius: 40rpx; display: flex; align-items: center; padding: 0 24rpx; }
.search-icon { flex-shrink: 0; width: 25rpx; height: 25rpx; border: 3rpx solid #a3a4aa; border-radius: 50%; margin-right: 18rpx; position: relative; }
.search-icon::after { content: ''; width: 11rpx; height: 3rpx; background: #a3a4aa; position: absolute; right: -8rpx; bottom: -4rpx; transform: rotate(45deg); }
.search-box input { flex: 1; min-width: 0; font-size: 26rpx; }
.clear-search { font-size: 22rpx; padding: 0 0 0 12rpx; line-height: 1.5; background: transparent; color: #888; }
.hero { position: relative; overflow: hidden; height: 350rpx; border-radius: 24rpx; margin-top: 8rpx; background: linear-gradient(110deg,#fa1d2a 5%,#ff3c19 50%,#ff9e30 100%); color: white; }
.hero-copy { position: relative; z-index: 2; padding: 30rpx 32rpx; }
.hero-kicker { display: block; font-size: 20rpx; color: #ffe0b5; letter-spacing: 2rpx; margin-bottom: 20rpx; }
.hero-title { display: block; font-size: 55rpx; font-weight: 800; line-height: 1.15; letter-spacing: -2rpx; text-shadow: 0 4rpx 0 #d83415; }
.hero-title > text { display: block; color: #fff1a6; }
.hero-sub { display: block; margin: 17rpx 0; font-size: 22rpx; color: #fff1df; }
.hero-cta { display: flex; justify-content: space-between; width: 205rpx; background: linear-gradient(#fff9d5,#ffd684); border-radius: 30rpx; color: #bd3418; padding: 12rpx 22rpx; font-size: 25rpx; font-weight: 700; box-shadow: 0 7rpx 0 #d43b1e66; }
.hero-circle { position: absolute; border: 35rpx solid #ffd87c30; border-radius: 50%; }
.hero-circle.one { width: 400rpx; height: 400rpx; right: -160rpx; top: -170rpx; }
.hero-circle.two { width: 500rpx; height: 500rpx; right: -90rpx; bottom: -370rpx; }
.hero-art { position: absolute; right: 22rpx; top: 80rpx; width: 230rpx; height: 245rpx; }
.ticket { width: 170rpx; height: 210rpx; padding: 18rpx 10rpx; margin-left: 22rpx; border: 6rpx solid #fff5c8; border-radius: 18rpx; color: #f34727; text-align: center; transform: rotate(12deg); background: linear-gradient(140deg,#fffde0,#ffda80); box-shadow: 7rpx 12rpx 0 #da331e55; font-size: 21rpx; }
.big-percent { display: block; font-size: 100rpx; line-height: 1.1; font-weight: 800; }
.ticket-bottom { display: block; border-top: 2rpx dashed #f2a44b; padding-top: 9rpx; font-size: 19rpx; }
.coin { position: absolute; right: 0; bottom: 1rpx; width: 88rpx; height: 88rpx; border-radius: 50%; background: #ffdf6f; border: 8rpx solid #ffecac; text-align: center; line-height: 72rpx; font-size: 45rpx; color: #ec9431; font-weight: 700; box-shadow: 0 7rpx 0 #d1441e55; }
.little-ticket { position: absolute; left: 0; bottom: 0; transform: rotate(-15deg); border-radius: 12rpx; color: #fff6c6; background: #f02532; padding: 12rpx 25rpx; border: 3rpx solid #ffb161; font-size: 36rpx; font-weight: 700; }
.entry-panel { position: relative; margin-top: -4rpx; padding: 34rpx 8rpx 12rpx; }
.entry-grid { display: flex; flex-wrap: wrap; }
.entry { width: 20%; background: transparent; padding: 0 0 28rpx; line-height: 1.5; display: flex; align-items: center; flex-direction: column; }
.entry-icon { position: relative; width: 86rpx; height: 86rpx; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: white; font-weight: 700; font-size: 47rpx; box-shadow: inset 0 -7rpx 0 #00000008; }
.entry-badge { position: absolute; top: -10rpx; right: -12rpx; padding: 2rpx 9rpx; font-size: 18rpx; font-weight: 400; background: #ff314c; border-radius: 12rpx 12rpx 12rpx 0; border: 2rpx solid #fff; }
.entry-name { font-size: 24rpx; font-weight: 600; color: #343438; margin-top: 14rpx; }
.benefits { display: flex; justify-content: space-around; color: #a18b7d; font-size: 20rpx; padding: 20rpx 8rpx 0; }
.articles { display: flex; gap: 14rpx; }
.article-card { flex: 1; min-width: 0; background: #fff; border-radius: 18rpx; padding: 0; text-align: left; line-height: 1.5; overflow: hidden; }
.article-cover { height: 178rpx; position: relative; padding-top: 32rpx; text-align: center; color: #fff; overflow: hidden; }
.article-tag { position: absolute; top: 0; left: 0; background: #ffffffde; color: #754936; padding: 4rpx 9rpx; font-size: 18rpx; border-radius: 0 0 10rpx 0; }
.cover-symbol { display: block; font-size: 72rpx; font-weight: 800; line-height: 1.2; text-shadow: 4rpx 4rpx 0 #00000012; }
.cover-sub { font-size: 18rpx; letter-spacing: 2rpx; color: #ffffffd9; }
.article-copy { padding: 16rpx 13rpx; color: #3b3b3b; font-size: 24rpx; font-weight: 600; }
.read { display: block; color: #ff633f; font-size: 19rpx; font-weight: 400; margin-top: 12rpx; }
.scenario { display: flex; align-items: center; text-align: left; padding: 24rpx; margin-bottom: 16rpx; line-height: 1.5; }
.scenario-icon { flex-shrink: 0; width: 92rpx; height: 92rpx; background: #fff0ec; color: #ff563b; border-radius: 18rpx; text-align: center; padding-top: 12rpx; font-size: 34rpx; font-weight: 700; }
.scenario-icon text { display: block; font-size: 20rpx; }
.scenario-icon.orange { color: #eb8c25; background: #fff4e3; }
.scenario-copy { flex: 1; padding: 0 20rpx; font-size: 25rpx; color: #3c3d41; font-weight: 600; }
.scenario-desc { display: block; font-size: 21rpx; color: #9b9b9b; font-weight: 400; margin-top: 8rpx; }
.scenario-arrow { color: #b8b8b8; font-size: 40rpx; }
.home-note { text-align: center; color: #b0b0b0; font-size: 21rpx; padding: 24rpx 0; }
.searching { margin-top: 16rpx; }
.search-title { padding: 0 18rpx 28rpx; font-size: 25rpx; color: #999; }
.no-results { font-size: 26rpx; color: #999; padding: 20rpx 24rpx 40rpx; text-align: center; }
</style>
