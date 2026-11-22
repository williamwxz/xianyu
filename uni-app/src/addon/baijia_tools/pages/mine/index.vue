<template>
  <view class="mine">
    <view class="top" :style="{ paddingTop: `${navTop + navHeight}px` }">
      <view class="profile">
        <image v-if="info && info.headimg" class="avatar" :src="img(info.headimg)" mode="aspectFill" />
        <view v-else class="avatar avatar-empty">省</view>
        <view class="profile-copy" @click="info ? go('/app/pages/member/personal') : login()">
          <text class="nickname">{{ info ? (info.nickname || '百迦用户') : '点击登录' }}</text>
          <text class="uid">{{ info ? `UID: ${info.member_no || info.member_id}` : '登录后查看余额、积分和收益' }}</text>
        </view>
        <view class="task" @click="need('/app/pages/member/sign_in')">做任务 ›</view>
      </view>
      <view v-if="progress" class="growth" @click="need('/app/pages/member/level')">
        <view class="track">
          <view class="track-fill" :style="{ width: `${progress.percent}%` }" />
          <view v-for="n in progress.nodes" :key="n.id" class="node" :class="{ reached: n.reached, current: n.current }" :style="{ left: `${n.left}%` }">
            <text v-if="n.current" class="node-tip">{{ n.name }}</text>
          </view>
        </view>
        <view class="growth-text">
          <text>成长值 <text class="growth-num">{{ progress.growth }}</text></text>
          <text>{{ progress.next ? `距${progress.next.name}还差${progress.next.gap}成长值` : '已达最高等级' }}</text>
        </view>
      </view>
    </view>

    <view class="panel">
      <view class="level" @click="need('/app/pages/member/level')">
        <text class="level-name">{{ (info && info.member_level_name) || '普通会员' }}</text>
        <text class="level-sub">当前等级</text>
        <view v-if="perks.length" class="perks">
          <view v-for="perk in perks" :key="perk.title" class="perk">
            <image v-if="perk.icon" class="perk-icon" :src="img(perk.icon)" mode="aspectFit" />
            <text>{{ perk.title }}</text>
          </view>
        </view>
      </view>
      <view class="stats">
        <view class="stat" @click="need('/app/pages/member/balance')"><text class="stat-num">{{ money(balanceTotal) }}</text><text class="stat-name">余额</text></view>
        <view class="stat" @click="need('/app/pages/member/point')"><text class="stat-num">{{ info ? (info.point || 0) : '--' }}</text><text class="stat-name">积分</text></view>
        <view class="stat" @click="need('/app/pages/member/commission')"><text class="stat-num">{{ money(info && info.commission) }}</text><text class="stat-name">佣金</text></view>
      </view>

      <view v-for="group in groups" :key="group.title" class="group">
        <view class="group-head">
          <text class="group-title">{{ group.title }}</text>
          <view class="group-more" @click="tap(group.more)"><text>{{ group.moreText }}</text><image class="chevron" src="/addon/baijia_tools/static/design/mine/chevron-right.png" /></view>
        </view>
        <view class="group-grid">
          <button v-for="item in group.items" :key="item.name" class="cell" :open-type="item.share ? 'share' : ''" @click="tap(item)">
            <image class="cell-icon" :src="`/addon/baijia_tools/static/design/mine/${item.icon}.png`" />
            <text class="cell-name">{{ item.name }}</text>
          </button>
        </view>
      </view>

      <view class="links">
        <view class="link-row" @click="need('/app/pages/member/address')"><text>收货地址</text><text class="arrow">›</text></view>
        <view class="link-row" @click="go('/app/pages/setting/index')"><text>设置</text><text class="arrow">›</text></view>
      </view>
    </view>
  </view>
  <BjTabbar current="mine" />
</template>

<script setup>
import { computed, ref } from 'vue'
import { onShow, onShareAppMessage } from '@dcloudio/uni-app'
import useMemberStore from '@/stores/member'
import { useLogin } from '@/hooks/useLogin'
import { img } from '@/utils/common'
import BjTabbar from '../../components/bj-tabbar.vue'
import { openService } from '../../lib/launch.js'
import { levelProgress, levelPerks } from '../../lib/level.mjs'
import request from '@/utils/request'

const memberStore = useMemberStore()
const info = computed(() => memberStore.info)
const balanceTotal = computed(() => info.value ? Number(info.value.balance || 0) + Number(info.value.money || 0) : null)
const money = (value) => (value === null || value === undefined) ? '--' : Number(value).toFixed(2)

const sys = uni.getSystemInfoSync()
const navTop = sys.statusBarHeight || 20
let navHeight = 44
// #ifdef MP-WEIXIN
const capsule = uni.getMenuButtonBoundingClientRect()
if (capsule && capsule.height) { navHeight = capsule.height + (capsule.top - navTop) * 2 }
// #endif

const ORDERS = '/addon/baijia_tools/pages/orders/index'
const groups = [
  { title: '订单中心', moreText: '全部', more: { url: ORDERS }, items: [
    { name: '我的消费', icon: 'orders', url: ORDERS },
    { name: '霸王餐', icon: 'bawangcan', service: 'bawangcan' },
    { name: '我的积分', icon: 'point', url: '/app/pages/member/point' },
    { name: '我的余额', icon: 'balance', url: '/app/pages/member/balance' }
  ] },
  { title: '推广管理', moreText: '进入', more: { service: 'distribution' }, items: [
    { name: '订单管理', icon: 'order-manage', service: 'distribution' },
    { name: '收益明细', icon: 'income', url: '/app/pages/member/commission' },
    { name: '我的粉丝', icon: 'fans', service: 'distribution' },
    { name: '邀请好友', icon: 'invite', share: true }
  ] },
  { title: '合伙人管理', moreText: '进入', more: { service: 'partner' }, items: [
    { name: '店铺订单', icon: 'shop', service: 'partner' },
    { name: '店铺收益', icon: 'shop-income', service: 'partner' },
    { name: '使用说明', icon: 'help', page: '/addon/baijia_tools/pages/about/index' },
    { name: '发圈素材', icon: 'material', service: 'partner' },
    { name: '店铺海报', icon: 'poster', service: 'partner' }
  ] }
]

// Levels, thresholds and perks are configured in the niucloud admin (会员等级).
const levels = ref([])
const progress = computed(() => info.value ? levelProgress(levels.value, info.value.growth) : null)
const perks = computed(() => {
  const id = info.value && info.value.member_level
  return levelPerks(levels.value.find((l) => l.level_id == id)).slice(0, 4)
})
onShow(() => {
  if (!memberStore.token) return
  memberStore.getMemberInfo()
  request.get('member/level', {}, { showErrorMessage: false }).then((res) => { levels.value = res.data || [] }).catch(() => {})
})

const go = (url) => uni.navigateTo({ url })
const login = () => useLogin().setLoginBack({ url: '/addon/baijia_tools/pages/mine/index' })
const need = (url) => (memberStore.token ? go(url) : login())
const tap = (item) => {
  if (item.share) return
  if (item.service) openService(item.service)
  else if (item.page) go(item.page)
  else need(item.url)
}

// Referral attribution belongs to the distribution module; this share is a plain invite.
onShareAppMessage(() => ({ title: '百迦好省 · 吃喝玩乐先省一笔', path: '/addon/baijia_tools/pages/home/index' }))
</script>

<style scoped>
@import "../../styles/base.css";
.mine { min-height: 100vh; background: #fffdf7; color: #202120; font-size: 28rpx; padding-bottom: calc(150rpx + env(safe-area-inset-bottom)); }
.top { padding-left: 32rpx; padding-right: 32rpx; padding-bottom: 90rpx; background: linear-gradient(180deg, #e6fc7b 0%, #fcf490 70%, #fffdf7 100%); }
.profile { display: flex; align-items: center; padding-top: 24rpx; }
.avatar { width: 112rpx; height: 112rpx; border-radius: 50%; border: 4rpx solid #fff; flex-shrink: 0; }
.avatar-empty { background: #202120; color: #d4fa20; font-size: 48rpx; font-weight: 800; text-align: center; line-height: 104rpx; }
.profile-copy { flex: 1; min-width: 0; padding-left: 24rpx; }
.nickname { display: block; font-size: 36rpx; font-weight: 800; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
.uid { display: block; font-size: 22rpx; color: #6b6d69; margin-top: 6rpx; }
.task { flex-shrink: 0; padding: 0 26rpx; height: 60rpx; line-height: 60rpx; border-radius: 30rpx; background: #202120; color: #fff; font-size: 24rpx; font-weight: 600; }
.growth { margin-top: 56rpx; }
.track { position: relative; height: 8rpx; margin: 0 12rpx; border-radius: 4rpx; background: rgba(255, 255, 255, .8); }
.track-fill { position: absolute; left: 0; top: 0; bottom: 0; border-radius: 4rpx; background: #202120; }
.node { position: absolute; top: 50%; width: 20rpx; height: 20rpx; margin: -10rpx 0 0 -10rpx; border-radius: 50%; background: #fff; border: 4rpx solid #d7d9d3; box-sizing: border-box; }
.node.reached { border-color: #202120; }
.node.current { background: #202120; }
.node-tip { position: absolute; bottom: 30rpx; left: 50%; transform: translateX(-50%); padding: 8rpx 18rpx; border-radius: 10rpx; background: #202120; color: #fff; font-size: 20rpx; white-space: nowrap; }
.growth-text { display: flex; justify-content: space-between; margin-top: 20rpx; font-size: 22rpx; color: #6b6d69; }
.growth-num { color: #202120; font-weight: 800; }
.perks { display: flex; flex-wrap: wrap; gap: 20rpx 40rpx; margin-top: 24rpx; }
.perk { display: flex; align-items: center; font-size: 24rpx; }
.perk-icon { width: 56rpx; height: 56rpx; margin-right: 12rpx; border-radius: 50%; background: #f7f5f2; }
.panel { position: relative; margin-top: -50rpx; padding: 0 32rpx; border-radius: 40rpx 40rpx 0 0; background: #fffdf7; }
.level { padding: 36rpx 0 8rpx; }
.level-name { display: block; font-size: 40rpx; font-weight: 800; }
.level-sub { display: block; font-size: 22rpx; color: #8a8c88; margin-top: 4rpx; }
.stats { display: flex; padding: 30rpx 0; border-bottom: 1rpx solid #ecebe6; }
.stat { flex: 1; text-align: center; }
.stat + .stat { border-left: 1rpx solid #ecebe6; }
.stat-num { display: block; font-size: 38rpx; font-weight: 800; }
.stat-name { display: block; font-size: 22rpx; color: #8a8c88; margin-top: 4rpx; }
.group { padding: 34rpx 0 6rpx; border-bottom: 1rpx solid #ecebe6; }
.group-head { display: flex; justify-content: space-between; align-items: center; }
.group-title { font-size: 30rpx; font-weight: 800; }
.group-more { display: flex; align-items: center; font-size: 22rpx; color: #8a8c88; }
.chevron { width: 28rpx; height: 28rpx; margin-left: 4rpx; }
.group-grid { display: flex; flex-wrap: wrap; margin-top: 12rpx; }
.cell { width: 25%; background: transparent; padding: 18rpx 0 22rpx; line-height: 1.4; display: flex; flex-direction: column; align-items: center; }
.cell-icon { width: 48rpx; height: 48rpx; }
.cell-name { font-size: 24rpx; color: #202120; margin-top: 10rpx; }
.links { padding: 12rpx 0; }
.link-row { display: flex; justify-content: space-between; padding: 26rpx 0; font-size: 27rpx; }
.link-row + .link-row { border-top: 1rpx solid #ecebe6; }
.arrow { color: #b9bbb7; }
</style>
