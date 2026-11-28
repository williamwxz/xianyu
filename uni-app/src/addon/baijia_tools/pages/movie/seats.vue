<template>
  <view class="page-wrap">
    <view v-if="map">
      <view class="title">{{ map.film }}</view>
      <view class="caption">{{ map.cinema }} · {{ map.time }}</view>
      <view class="screen">{{ map.hall }} · 银幕方向</view>
      <scroll-view scroll-x class="theater">
        <view :style="{width: width + 'rpx'}">
          <view v-for="row in rows" :key="row.number" class="seat-row">
            <text class="row-label">{{ row.seats[0].label.split('排')[0] }}排</text>
            <view v-for="s in row.seats" :key="s.id" class="seat" :class="{ sold: !s.available, chosen: selected.includes(s.id) }"
              :style="{left: 62 + (s.column - firstColumn) * 68 + 'rpx'}" @click="toggle(s)">{{ s.label.replace(/^.*排/,'').replace(/座$/,'') }}</view>
          </view>
        </view>
      </scroll-view>
      <view class="caption">灰色不可选 · 绿色已选 · 每单限 {{ map.limit }} 张</view>
      <view v-if="selectedSeats.length" class="summary">
        <view v-for="s in selectedSeats" :key="s.id">{{ s.label }}{{ s.love ? '（情侣座）' : '' }}　¥{{ money(s.fen) }}</view>
      </view>
      <view class="field"><text>取票手机号</text><input type="number" maxlength="11" v-model="phone" placeholder="请输入手机号" /></view>
      <button :disabled="busy || !selected.length" class="confirm" @click="checkout">确认座位 · ¥{{ money(total) }}</button>
      <view class="caption">确认后可查看报价，付款前座位可能变动。</view>
    </view>
    <view v-else-if="busy" class="caption">正在读取座位图…</view>
    <button v-else @click="load">重新加载座位图</button>
    <button v-if="map" class="refresh" :disabled="busy" @click="load">刷新座位</button>
  </view>
</template>
<script setup>
import { computed, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import request from '@/utils/request'
import useMemberStore from '@/stores/member'
import { useLogin } from '@/hooks/useLogin'
const map = ref(null), selected = ref([]), phone = ref(''), busy = ref(false)
const member = useMemberStore()
let args = {}, lastInput = '', key = ''
const rows = computed(() => {
  const groups = new Map()
  for (const s of map.value?.seats || []) {
    if (!groups.has(s.row)) groups.set(s.row, [])
    groups.get(s.row).push(s)
  }
  return Array.from(groups, ([number,seats]) => ({number,seats})).sort((a,b) => a.number-b.number)
})
const firstColumn = computed(() => Math.min(...(map.value?.seats || []).map(s => s.column)))
const width = computed(() => map.value?.seats.length ? 62+68*(1+Math.max(...map.value.seats.map(s=>s.column))-firstColumn.value) : 0)
const selectedSeats = computed(() => (map.value?.seats || []).filter(s => selected.value.includes(s.id)))
const total = computed(() => selectedSeats.value.reduce((sum,s)=>sum+s.fen,0))
const money = fen => (fen/100).toFixed(2)
const toast = title => uni.showToast({title,icon:'none'})
async function load() {
  busy.value = true
  selected.value = []
  map.value = null
  try { map.value = (await request.get('baijia_tools/movie/seats',args)).data }
  finally { busy.value = false }
}
function toggle(s) {
  if (busy.value || !s.available) return
  if (selected.value.includes(s.id)) selected.value = selected.value.filter(id=>id!==s.id)
  else if (selected.value.length < map.value.limit) selected.value.push(s.id)
  else toast('已达本场购票上限')
}
async function checkout() {
  if (busy.value) return
  if (!member.token) return useLogin().setLoginBack({url:'/addon/baijia_tools/pages/movie/seats',param:args})
  if (!/^1\d{10}$/.test(phone.value)) return toast('请输入有效手机号')
  const input = JSON.stringify([selected.value,phone.value,total.value])
  if (lastInput !== input) {
    lastInput = input
    key = Date.now().toString(36) + '-' + Math.random().toString(36).slice(2) + '-' + Math.random().toString(36).slice(2)
  }
  busy.value = true
  try {
    const {data} = await request.post('baijia_tools/movie/checkout',{...args,phone:phone.value,seats:selected.value,amount_fen:total.value,request_key:key})
    uni.navigateTo({url:'./ticket?sn='+encodeURIComponent(data.id)})
  } finally { busy.value = false }
}
onLoad(q => { args = {cinema:q.cinema,film:q.film,show:q.show}; load() })
</script>
<style scoped>
@import "../../styles/base.css";
.title { font-size:38rpx; font-weight:700 }
.caption,.refresh { color:#64665e; font-size:24rpx; margin:16rpx 0 }
.screen { margin:42rpx auto 24rpx; width:70%; text-align:center; border-top:8rpx solid #c8cbc0; color:#666 }
.theater { background:#fff; padding:18rpx 0 }
.seat-row { position:relative; height:62rpx }
.row-label { color:#6a705f; font-size:20rpx; line-height:56rpx }
.seat { position:absolute; top:4rpx; width:56rpx; height:46rpx; border:2rpx solid #879967; border-radius:10rpx; text-align:center; line-height:46rpx; font-size:20rpx }
.sold { background:#ddd; border-color:#ddd; color:#888 }
.chosen,.confirm { background:#d4fa20; color:#202120 }
.summary,.field { background:white; padding:22rpx; margin:18rpx 0; border-radius:20rpx }
.field { display:flex; gap:18rpx }
.confirm { border-radius:40rpx; font-weight:650 }
</style>
