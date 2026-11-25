<template>
  <view>
      <view v-if="keyword && !horizontal" class="search-note">「{{ keyword }}」的搜索结果</view>
      <scroll-view v-else-if="!horizontal" scroll-x class="tabs" :show-scrollbar="false">
        <view v-for="t in TOPICS" :key="t.topic" class="tab" :class="{ active: t.topic === topic }" @click="switchTopic(t.topic)">{{ t.name }}</view>
      </scroll-view>

      <scroll-view :scroll-x="horizontal" :class="{ shelf: horizontal }">
      <view class="products">
        <view v-for="item in products" :key="item.id" class="product" @click="openMeituanProduct(item)">
          <image v-if="item.image" class="photo" :src="item.image" mode="aspectFill" />
          <view v-else class="photo photo-empty">团购</view>
          <view class="product-info">
            <text class="product-name">{{ item.name }}</text>
            <text v-if="item.sales" class="product-meta">{{ item.sales }}</text>
            <view class="prices">
              <text class="price"><text class="yen">¥</text>{{ item.price }}</text>
              <text v-if="Number(item.original_price) > Number(item.price)" class="original">¥{{ item.original_price }}</text>
            </view>
          </view>
        </view>
      </view>
      </scroll-view>
      <text v-if="loading" class="hint">正在加载优惠…</text>
      <view v-else-if="error" class="hint" @click="loadProducts(hasMore)">{{ error }} · 点击重试</view>
      <view v-else-if="hasMore && !horizontal" class="load-more" @click="loadProducts(true)">加载更多</view>
      <text v-else-if="!products.length && !hasMore" class="hint">{{ keyword ? '没有找到相关商品，换个关键词试试' : '当前城市暂无该榜单商品' }}</text>
  </view>
</template>
<script setup>
import { ref, watch } from "vue"
import request from "@/utils/request"
import { openMeituanProduct } from "../lib/launch.js"
const props = defineProps({ horizontal: Boolean, cityId: String, keyword: { type: String, default: "" } })
// Server allows topics 2 / 3 / 5 (see MEITUAN.md).
const TOPICS = [{ topic: 2, name: '今日必推' }, { topic: 3, name: '同城优选' }, { topic: 5, name: '实时热销' }]
const topic = ref(2)
const products = ref([])
// Keyword search replaces the topic feed until cleared (the server's keyword search covers all Meituan categories).
const loading = ref(false)
const error = ref('')
let version = 0
// append=true loads the next search page (topic lists are fixed at 20 items).
const cursor = ref('')
const hasMore = ref(false)
async function loadProducts(append = false) {
  if (append && (loading.value || !hasMore.value)) return
  const v = ++version
  loading.value = true; error.value = ''
  if (!append) { products.value = []; cursor.value = ''; hasMore.value = false }
  try {
    const params = props.keyword
      ? { city_id: props.cityId, keyword: props.keyword, cursor: append ? cursor.value : '' }
      : { city_id: props.cityId, topic: topic.value }
    const res = await request.get('baijia_tools/meituan/products', params, { showErrorMessage: false })
    if (v !== version) return
    const data = res.data || {}
    const seen = new Set(append ? products.value.map((p) => p.id) : [])
    const items = (data.items || []).filter((p) => { if (seen.has(p.id)) return false; seen.add(p.id); return true })
    products.value = append ? products.value.concat(items) : items
    hasMore.value = Boolean(props.keyword && data.has_more && data.cursor && data.cursor !== cursor.value)
    cursor.value = data.cursor || ''
  } catch (e) { if (v === version) error.value = '暂时无法加载' }
  finally { if (v === version) loading.value = false }
}
const switchTopic = (t) => { if (t !== topic.value) { topic.value = t; loadProducts() } }
watch(() => [props.cityId, props.keyword], () => loadProducts(), { immediate: true })
</script>
<style scoped>
.search-note { margin-top: 20rpx; font-size: 26rpx; }
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
.load-more { margin: 8rpx auto 0; width: 260rpx; height: 64rpx; line-height: 64rpx; border-radius: 32rpx; background: #202120; color: #d4fa20; font-size: 24rpx; text-align: center; }
.hint { display: block; text-align: center; font-size: 24rpx; color: #8a8c88; padding: 24rpx 0; }
.shelf .products { flex-wrap: nowrap; justify-content: flex-start; gap: 16rpx; }
.shelf .product { flex: 0 0 220rpx; margin-bottom: 0; }
.shelf .photo { width: 220rpx; height: 112rpx; }
.shelf .product-name { max-width: 184rpx; }
</style>
