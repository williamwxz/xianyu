<template>
  <view class="page-wrap">
    <view class="banner"><text class="banner-kicker">吃喝玩乐 · 美团优惠</text><text class="banner-title">发现同城好价</text><text class="banner-pill">优惠以美团购买页为准</text></view>
    <view class="search-card white-card">
      <view class="search-head"><text class="search-name">到店团购</text>
        <picker :range="cities" range-key="label" :value="cityIndex" @change="changeCity"><view class="place">{{ city.name }} ▾</view></picker>
      </view>
      <text class="hint">当前城市：{{ city.name }}，可点击右上角切换</text>
      <view class="search-row"><input v-model="keyword" class="search-input" :focus="focus" maxlength="100" confirm-type="search" placeholder="搜索美团商品（全品类）" @confirm="search" /><button class="search-btn" @click="search">搜索</button></view>
      <button v-if="searched" class="back" @click="clearSearch">返回团购榜单</button>
    </view>
    <view class="quick"><button v-for="item in quick" :key="item.id" class="quick-item white-card" @click="openService(item.id)"><view class="quick-icon" :style="{ background: item.color }">{{ item.icon }}</view><text class="quick-name">{{ item.name }}</text></button></view>
    <view v-if="searched" class="block white-card">
      <view class="block-head"><text class="block-title">搜索结果</text><text class="block-note">美团全品类</text></view>
      <MeituanProducts :items="results" @open="openProduct" />
      <text v-if="searchBusy" class="hint">正在查找优惠…</text>
      <button v-else-if="searchError" class="retry" @click="runSearch(false)">{{ searchError }} · 重试</button>
      <text v-else-if="!results.length" class="hint">未找到相关商品，试试其他关键词。</text>
      <button v-else-if="hasMore" class="retry" @click="runSearch(true)">加载更多</button>
    </view>
    <view v-else v-for="block in blocks" :key="block.topic" class="block white-card">
      <view class="block-head"><text class="block-title">{{ block.title }}</text><text class="block-note">{{ city.name }}</text></view>
      <MeituanProducts :items="block.items" @open="openProduct" />
      <text v-if="block.loading" class="hint">正在加载优惠…</text>
      <button v-else-if="block.error" class="retry" @click="loadBlock(block)">{{ block.error }} · 重试</button>
      <text v-else-if="!block.items.length" class="hint">当前城市暂无该榜单商品。</text>
    </view>
  </view><tabbar />
</template>
<script setup>
import { ref, computed } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import request from '@/utils/request'
import MeituanProducts from '../../components/meituan-products.vue'
import cityData from '../../lib/meituan-cities.json'
import { getService, resolveLink } from '../../lib/services.mjs'
import { openService } from '../../lib/launch.js'
const cities = cityData.map(c => ({ ...c, label: `${c.province} · ${c.name}` }))
const initialCity = uni.getStorageSync('baijia_meituan_city') || 'WKV2HMXUEK634WP64CUCUQGM64'
const cityIndex = ref(Math.max(0, cities.findIndex(c => c.id === initialCity)))
const city = computed(() => cities[cityIndex.value])
const keyword = ref(''), activeKeyword = ref(''), focus = ref(false), searched = ref(false)
const results = ref([]), searchBusy = ref(false), searchError = ref(''), cursor = ref(''), hasMore = ref(false)
let searchVersion = 0, opening = false
const quick = ['daodian', 'waimai', 'bawangcan', 'movie'].map(getService)
const blocks = ref([{ topic:2, title:'今日必推' }, { topic:5, title:'实时热销' }, { topic:3, title:'同城优选' }].map(b => ({ ...b, items:[], loading:false, error:'', version:0 })))
async function loadBlock(block) {
  const version = ++block.version
  block.loading = true; block.error = ''; block.items = []
  try {
    const res = await request.get('baijia_tools/meituan/products', { city_id:city.value.id, topic:block.topic }, { showErrorMessage:false })
    if (version === block.version) block.items = res.data.items || []
  } catch (e) { if (version === block.version) block.error = '暂时无法加载' }
  finally { if (version === block.version) block.loading = false }
}
function loadBoards() { blocks.value.forEach(loadBlock) }
function changeCity(e) {
  cityIndex.value = Number(e.detail.value); uni.setStorageSync('baijia_meituan_city', city.value.id)
  loadBoards(); if (searched.value) runSearch(false)
}
function search() {
  const q = keyword.value.trim()
  if (!q) { clearSearch(); return }
  activeKeyword.value = q; searched.value = true; runSearch(false)
}
function clearSearch() { ++searchVersion; searched.value = false; searchBusy.value = false; keyword.value = ''; results.value = [] }
async function runSearch(append) {
  if (append && searchBusy.value) return
  const version = ++searchVersion
  searchBusy.value = true; searchError.value = ''
  if (!append) { results.value = []; cursor.value = ''; hasMore.value = false }
  try {
    const res = await request.get('baijia_tools/meituan/products', { city_id:city.value.id, keyword:activeKeyword.value, cursor:append ? cursor.value : '' }, { showErrorMessage:false })
    if (version !== searchVersion) return
    const seen = new Set(append ? results.value.map(p => p.id) : [])
    const items = (res.data.items || []).filter(p => { if (seen.has(p.id)) return false; seen.add(p.id); return true })
    results.value = append ? [...results.value, ...items] : items
    hasMore.value = Boolean(res.data.has_more && res.data.cursor && res.data.cursor !== cursor.value)
    cursor.value = res.data.cursor || ''
  } catch (e) { if (version === searchVersion) searchError.value = '搜索暂不可用' }
  finally { if (version === searchVersion) searchBusy.value = false }
}
async function openProduct(item) {
  if (opening) return
  opening = true; uni.showLoading({ title:'正在打开' })
  try {
    const res = await request.get('baijia_tools/meituan/link', { id:item.id, platform:item.platform, biz_line:item.biz_line ?? '' })
    let mini = false
    // #ifdef MP-WEIXIN
    mini = true
    // #endif
    const target = resolveLink(res.data, mini)
    if (!target) throw new Error('empty link')
    if (target.type === 'miniProgram') {
      uni.navigateToMiniProgram({ appId:target.appId, path:target.path, fail: () => uni.showToast({ title:'未能打开美团，请重试', icon:'none' }) })
    } else if (target.type === 'redirect') {
      // #ifdef H5
      window.location.href = target.url
      // #endif
    } else if (target.type === 'copy') { uni.setClipboardData({ data:target.url }) }
  } catch (e) { uni.showToast({ title:'商品暂不可购买，请稍后重试', icon:'none' }) }
  finally { uni.hideLoading(); opening = false }
}
onLoad(query => { focus.value = Boolean(query && query.focus); loadBoards() })
</script>
<style scoped>
@import "../../styles/base.css";
.banner { border-radius:24rpx; padding:36rpx 32rpx; background:linear-gradient(120deg,#19b67a,#8fe0b5); color:white; }.banner text { display:block; }.banner-kicker { font-size:24rpx; }.banner-title { font-size:44rpx; font-weight:800; margin:10rpx 0 20rpx; }.banner-pill { font-size:23rpx; }
.search-card { margin-top:22rpx; padding:28rpx 24rpx; }.search-head,.block-head { display:flex; justify-content:space-between; align-items:center; }.search-name,.block-title { font-size:32rpx; font-weight:700; }.place { color:#159c6a; font-size:25rpx; padding:10rpx; }.search-row { display:flex; gap:16rpx; margin-top:16rpx; }.search-input { flex:1; min-width:0; height:76rpx; border-radius:38rpx; background:#f4f5f7; padding:0 24rpx; font-size:24rpx; }.search-btn { width:130rpx; height:76rpx; line-height:76rpx; border-radius:38rpx; background:#19b67a; color:white; font-size:26rpx; }
.quick { display:flex; gap:16rpx; margin-top:22rpx; }.quick-item { flex:1; padding:22rpx 0; display:flex; flex-direction:column; align-items:center; line-height:1.4; }.quick-icon { width:76rpx; height:76rpx; border-radius:22rpx; color:white; font-size:34rpx; font-weight:700; text-align:center; line-height:76rpx; }.quick-name { font-size:24rpx; margin-top:10rpx; }
.block { margin-top:22rpx; padding:26rpx 24rpx; }.block-title { color:#ef6337; }.block-note { font-size:23rpx; color:#999; }.hint { display:block; font-size:24rpx; color:#888; padding:16rpx 0; }.retry,.back { color:#159c6a; background:#f4faf7; font-size:25rpx; margin-top:16rpx; }
</style>
