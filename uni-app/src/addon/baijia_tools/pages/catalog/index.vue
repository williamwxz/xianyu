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
    <view v-if="filmMatches.length" class="matches">
      <text v-for="film in filmMatches" :key="film.id" @click="showCinemas(film)">《{{ film.name }}》 · 查影院 ›</text>
    </view>
    <ProductFeed v-if="cityId" :city-id="cityId" :keyword="keyword" />
  </view>
</template>
<script setup>
import { computed, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import request from '@/utils/request'
import cities from '../../lib/meituan-cities.json'
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
const films = ref([])
const filmMatches = computed(() => {
  if (!keyword.value) return []
  return films.value.filter(row => keyword.value.includes(row.name) || row.name.includes(keyword.value)).slice(0, 8)
})
let filmRequest = null
function submit() {
  keyword.value = productKeyword(draft.value)
  if (!keyword.value || filmRequest || films.value.length) return
  const city = cities.find(c => c.id === cityId.value)
  if (!city) return
  filmRequest = request.get('baijia_tools/native/movies', { city: city.name }, { showErrorMessage: false })
    .then(({ data }) => { films.value = data.list })
    .catch(() => { films.value = [] })
    .finally(() => { filmRequest = null })
}
function showCinemas(film) {
  uni.navigateTo({ url: '/addon/baijia_tools/pages/movie/index?city=' + cityId.value + '&film=' + encodeURIComponent(film.id) + '&filmName=' + encodeURIComponent(film.name) })
}
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
