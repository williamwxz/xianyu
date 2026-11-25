<template>
  <view class="page-wrap">
    <button v-if="member.info?.member_id" class="invite" open-type="share">邀请好友</button>
    <view class="row" @click="go('/app/pages/member/commission')">我的佣金明细 ›</view>
    <view class="row" @click="go('/addon/baijia_tools/pages/orders/index')">本人消费返利 · 待到账及已到账 ›</view>
    <view class="heading">我邀请的好友 {{ total ?? '' }}</view>
    <text v-for="fan in fans" :key="fan.member_id" class="row">{{ fan.nickname || '的确省用户' }}</text>
    <text v-if="loading" class="row">加载中…</text>
    <text v-else-if="error" class="row" @click="load">加载失败，点击重试</text>
    <text v-else-if="total === 0" class="row">还没有好友通过你的邀请注册</text>
    <button v-else-if="more" @click="load">加载更多</button>
  </view>
</template>
<script setup>
import { ref } from 'vue'
import { onLoad, onShareAppMessage } from '@dcloudio/uni-app'
import request from '@/utils/request'
import useMemberStore from '@/stores/member'
import { useLogin } from '@/hooks/useLogin'
const member = useMemberStore()
const fans = ref([])
const total = ref(null)
const more = ref(false)
const loading = ref(false)
const error = ref(false)
let page = 1
async function load() {
  if (loading.value) return
  loading.value = true
  error.value = false
  try {
    const { data } = await request.get('baijia_tools/promotion/fans', { page })
    fans.value.push(...data.list)
    total.value = data.total
    more.value = data.has_more
    page++
  } catch { error.value = true }
  finally { loading.value = false }
}
const go = url => uni.navigateTo({ url })
onLoad(() => {
  if (!member.token) return useLogin().setLoginBack({ url: '/addon/baijia_tools/pages/promotion/index' })
  member.getMemberInfo()
  load()
})
onShareAppMessage(() => ({
  title: '和我一起在的确省找优惠',
  path: '/addon/baijia_tools/pages/home/index' + (member.info?.member_id ? '?mid=' + member.info.member_id : '')
}))
</script>
<style scoped>
@import "../../styles/base.css";
.invite { background: #202120; color: #d4fa20; border-radius: 40rpx; }
.heading { font-weight: 700; margin: 44rpx 0 12rpx; }
.row { display: block; background: white; padding: 24rpx; border-bottom: 1rpx solid #eee; }
</style>
