<template>
  <view class="page-wrap">
    <picker v-if="!film" :range="cities" range-key="name" :value="cityIndex" @change="changeCity"><view class="city">{{ city.name }} ﹀</view></picker>
    <view v-if="film" class="crumb">{{ filmName }}<text v-if="cinema"> · {{ cinemaName }}</text></view>
    <view class="section-head"><text class="heading">{{ cinema ? '放映场次' : film ? '选择影院' : '正在热映' }}</text></view>
    <view v-if="!film" class="search"><input v-model="keyword" placeholder="搜索影片" confirm-type="search" /></view>
    <view v-for="row in visible" :key="row.id" class="white-card movie-row" @click="next(row)">
      <image v-if="row.poster" class="poster" :src="row.poster" mode="aspectFill" lazy-load />
      <view class="copy">
        <view class="name">{{ cinema ? row.time : row.name }}</view>
        <view class="detail">{{ cinema ? [row.name,row.version].filter(Boolean).join(' · ') : row.address || row.genre }}</view>
        <view v-if="row.duration" class="detail">{{ row.duration }} 分钟</view>
      </view>
      <text class="action">{{ cinema ? '选座' : film ? '看场次' : '选影院' }}</text>
    </view>
    <view v-if="loading" class="hint">正在加载…</view>
    <button v-else-if="error" @click="load">加载失败，点击重试</button>
    <view v-else-if="!visible.length" class="hint">{{ cinema ? '暂无放映场次' : film ? '暂无可选影院' : '暂无匹配影片' }}</view>
    <button v-if="more && !loading && !error" @click="load">更多影院</button>
  </view>
</template>
<script setup>
import { ref, computed } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import cities from '../../lib/meituan-cities.json'
import request from '@/utils/request'
const cityIndex = ref(0)
const city = computed(() => cities[cityIndex.value])
const film = ref('')
const cinema = ref('')
const filmName = ref('')
const cinemaName = ref('')
const keyword = ref('')
const list = ref([])
const visible = computed(() => film.value ? list.value : list.value.filter(row => row.name.includes(keyword.value.trim())))
const loading = ref(false)
const more = ref(false)
const error = ref(false)
let version = 0
let page = 1
async function load() {
  if (loading.value) return
  const current = version
  loading.value = true
  error.value = false
  try {
    const { data } = await request.get('baijia_tools/native/movies', { city: city.value.name, film: film.value, cinema: cinema.value, page }, { showErrorMessage: false })
    if (current !== version) return
    const ids = new Set(list.value.map(row => row.id))
    list.value.push(...data.list.filter(row => !ids.has(row.id)))
    more.value = data.has_more
    page++
  } catch { if (current === version) error.value = true }
  finally { if (current === version) loading.value = false }
}
function changeCity({ detail }) {
  if (!cities[detail.value]) return
  version++
  cityIndex.value = Number(detail.value)
  list.value = []
  page = 1
  more.value = loading.value = false
  load()
}
function next(row) {
  if (cinema.value) return uni.navigateTo({url:'./seats?cinema='+encodeURIComponent(cinema.value)+'&film='+encodeURIComponent(film.value)+'&show='+encodeURIComponent(row.id)})
  const args = { city: city.value.id, film: film.value || row.id, filmName: film.value ? filmName.value : row.name }
  if (film.value) Object.assign(args, { cinema: row.id, cinemaName: row.name })
  uni.navigateTo({ url: './index?' + Object.entries(args).map(([key, value]) => key + '=' + encodeURIComponent(value)).join('&') })
}
function queryText(text) {
  // #ifdef MP-WEIXIN
  try { return decodeURIComponent(text || '') } catch { return '' }
  // #endif
  return text || ''
}
onLoad(query => {
  const id = query?.city || uni.getStorageSync('baijia_meituan_city') || 'WKV2HMXUEK634WP64CUCUQGM64'
  cityIndex.value = Math.max(0, cities.findIndex(c => c.id === id))
  film.value = query?.film || ''
  cinema.value = query?.cinema || ''
  keyword.value = queryText(query?.q)
  filmName.value = queryText(query?.filmName)
  cinemaName.value = queryText(query?.cinemaName)
  uni.setNavigationBarTitle({ title: cinema.value ? '放映场次' : film.value ? '影院' : '热门电影' })
  load()
})
</script>
<style scoped>
@import "../../styles/base.css";
.city, .crumb { font-size: 28rpx; font-weight: 650; }
.search, .movie-row { background: #fff; padding: 22rpx; border-radius: 24rpx; margin-bottom: 16rpx; }
.movie-row { display: flex; align-items: center; gap: 22rpx; }
.poster { width: 96rpx; height: 134rpx; border-radius: 12rpx; background: #f2f1e9; flex-shrink: 0; }
.copy { flex: 1; }
.name { font-size: 30rpx; font-weight: 700; }
.detail, .hint { margin-top: 8rpx; font-size: 24rpx; color: #62675f; }
.action { background: #d4fa20; font-size: 24rpx; border-radius: 30rpx; padding: 10rpx 18rpx; }
.hint { text-align: center; padding: 24rpx; }
</style>
