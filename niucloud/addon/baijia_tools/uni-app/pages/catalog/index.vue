<template>
  <view class="catalog">
    <view class="box">
      <input v-model="draft" :focus="focused" confirm-type="search" maxlength="100" placeholder="搜索商品、品牌和服务" @confirm="submit" />
      <text v-if="keyword || draft" class="clear" @click="clearSearch">清除</text>
      <text @click="submit">搜索</text>
    </view>
    <view v-if="matches.length" class="matches">
      <text v-for="item in matches" :key="item.id" @click="openService(item.id)">{{ item.name }} ›</text>
    </view>
    <ProductFeed v-if="cityId" :city-id="cityId" :keyword="keyword" />
  </view>
</template>
<script setup>
import { computed, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import ProductFeed from '../../components/product-feed.vue'
import { openService } from '../../lib/launch.js'
import { searchServices, productKeyword } from '../../lib/services.mjs'
const cityId = ref('')
const focused = ref(false)
const keyword = ref('')
const draft = ref('')
const matches = computed(() => searchServices(keyword.value))
function clearSearch() {
  draft.value = ''
  keyword.value = ''
}
function submit() { keyword.value = productKeyword(draft.value) }
onLoad(query => {
  cityId.value = query?.city || uni.getStorageSync('baijia_meituan_city') || 'WKV2HMXUEK634WP64CUCUQGM64'
  focused.value = Boolean(query?.search)
  if (focused.value) uni.setNavigationBarTitle({ title: '搜索' })
})
</script>
<style scoped>
.catalog { min-height: 100vh; padding: 24rpx; background: #fffaf0; }
.box, .matches { display: flex; align-items: center; padding: 18rpx 24rpx; border-radius: 28rpx; background: white; font-size: 28rpx; }
.box input { flex: 1; min-width: 0; }
.box text { font-weight: 700; }
.clear { padding: 0 20rpx; }
.matches { margin-top: 20rpx; gap: 24rpx; flex-wrap: wrap; }
</style>
