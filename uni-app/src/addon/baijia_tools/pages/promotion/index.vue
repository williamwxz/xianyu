<template>
  <view v-if="!member.token" class="page-wrap">
    <button class="invite" @click="login">登录查看推广中心</button>
  </view>
  <view v-else class="page-wrap">
    <button v-if="member.info?.member_id" class="invite" open-type="share">邀请好友</button>
    <view class="row" @click="go('/app/pages/member/commission')">我的佣金明细 ›</view>
    <view class="row" @click="go('/addon/baijia_tools/pages/orders/index')">本人消费返利 · 待到账及已到账 ›</view>
    <view v-if="report" class="row">好友消费 {{ report.orders }} 笔 · 已入账 ¥{{ report.promotion_credited }} · 待入账 ¥{{ report.promotion_pending }}</view>
    <MemberOrderList :key="refresh" title="好友推广订单" promotion />
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
import { onShow, onShareAppMessage } from '@dcloudio/uni-app'
import request from '@/utils/request'
import useMemberStore from '@/stores/member'
import { useLogin } from '@/hooks/useLogin'
import MemberOrderList from '../../components/member-order-list.vue'
const report = ref(null)
const refresh = ref(0)
const member = useMemberStore()
const fans = ref([])
const total = ref(null)
const more = ref(false)
const loading = ref(false)
const error = ref(false)
let version = 0
let page = 1
async function load() {
  if (loading.value || !member.token) return
  const current = version
  loading.value = true
  error.value = false
  try {
    const { data } = await request.get('baijia_tools/promotion/fans', { page })
    if (current !== version) return
    fans.value.push(...data.list)
    total.value = data.total
    more.value = data.has_more
    page++
  } catch { if (current === version) error.value = true }
  finally { if (current === version) loading.value = false }
}
const go = url => uni.navigateTo({ url })
const login = () => useLogin().setLoginBack({ url: '/addon/baijia_tools/pages/promotion/index' })
onShow(() => {
  const cycle = ++refresh.value
  report.value = null
  version++
  page = 1
  fans.value = []
  total.value = null
  more.value = false
  loading.value = false
  error.value = false
  if (!member.token) return
  request.get('baijia_tools/distribution/summary', {}, { showErrorMessage: false })
    .then(({ data }) => { if (cycle === refresh.value) report.value = data })
    .catch(() => { if (cycle === refresh.value) report.value = null })
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
