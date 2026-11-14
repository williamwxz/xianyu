<template>
  <view class="page-wrap">
    <view class="profile">
      <image v-if="info && info.headimg" class="avatar" :src="img(info.headimg)" mode="aspectFill" />
      <view v-else class="avatar avatar-empty">省</view>
      <view class="profile-copy" @click="info ? go('/app/pages/member/personal') : login()">
        <text class="nickname">{{ info ? (info.nickname || '百迦用户') : '点击登录' }}</text>
        <text class="profile-sub">{{ info ? `会员编号 ${info.member_no || info.member_id}` : '登录后查看余额、积分和收益' }}</text>
      </view>
      <button class="sign" @click="need('/app/pages/member/sign_in')">签到</button>
    </view>

    <view class="stats white-card">
      <button class="stat" @click="need('/app/pages/member/balance')"><text class="stat-num">{{ money(balanceTotal) }}</text><text class="stat-name">余额</text></button>
      <button class="stat" @click="need('/app/pages/member/point')"><text class="stat-num">{{ info ? (info.point || 0) : '--' }}</text><text class="stat-name">积分</text></button>
      <button class="stat" @click="need('/app/pages/member/commission')"><text class="stat-num">{{ money(info && info.commission) }}</text><text class="stat-name">佣金</text></button>
    </view>

    <view class="vip" @click="need('/app/pages/member/level')">
      <view><text class="vip-title">VIP {{ (info && info.member_level_name) || '普通会员' }}</text><text class="vip-sub">完成任务提升等级，享更多权益</text></view>
      <text class="vip-btn">做任务 ›</text>
    </view>

    <view v-for="group in groups" :key="group.title" class="group white-card">
      <view class="group-head"><text class="group-title">{{ group.title }}</text><text class="group-note">{{ group.note }}</text></view>
      <view class="group-grid">
        <button v-for="item in group.items" :key="item.name" class="cell" :open-type="item.share ? 'share' : ''" @click="tap(item)">
          <view class="cell-icon" :style="{ color: item.color, background: item.bg }">{{ item.icon }}</view>
          <text class="cell-name">{{ item.name }}</text>
        </button>
      </view>
    </view>

    <view class="footer-links white-card">
      <button class="link-row" @click="need('/app/pages/member/address')"><text>收货地址</text><text class="arrow">›</text></button>
      <button class="link-row" @click="go('/addon/baijia_tools/pages/about/index')"><text>使用说明与隐私</text><text class="arrow">›</text></button>
      <button class="link-row" @click="go('/app/pages/setting/index')"><text>设置</text><text class="arrow">›</text></button>
    </view>
  </view>
  <tabbar />
</template>

<script setup>
import { computed } from 'vue'
import { onShow, onShareAppMessage } from '@dcloudio/uni-app'
import useMemberStore from '@/stores/member'
import { useLogin } from '@/hooks/useLogin'
import { img } from '@/utils/common'
import { openService } from '../../lib/launch.js'

const memberStore = useMemberStore()
const info = computed(() => memberStore.info)
const balanceTotal = computed(() => info.value ? Number(info.value.balance || 0) + Number(info.value.money || 0) : null)
const money = (value) => (value === null || value === undefined) ? '--' : Number(value).toFixed(2)

const groups = [
  { title: '订单中心', note: '消费明细', items: [
    { name: '返利订单', icon: '单', color: '#ff7a1c', bg: '#fff3e8', url: '/addon/baijia_tools/pages/orders/index' },
    { name: '霸王餐', icon: '餐', color: '#ff5a2c', bg: '#fff0eb', service: 'bawangcan' },
    { name: '我的积分', icon: '积', color: '#e8384f', bg: '#ffecef', url: '/app/pages/member/point' },
    { name: '我的余额', icon: '余', color: '#13a06a', bg: '#e9f8f1', url: '/app/pages/member/balance' }
  ] },
  { title: '推广管理', note: '分销中心', items: [
    { name: '推广订单', icon: '单', color: '#f0a020', bg: '#fff7e6', service: 'distribution' },
    { name: '收益明细', icon: '益', color: '#e8384f', bg: '#ffecef', url: '/app/pages/member/commission' },
    { name: '我的粉丝', icon: '粉', color: '#ff3d6e', bg: '#ffedf2', service: 'distribution' },
    { name: '邀请好友', icon: '邀', color: '#2f7cf6', bg: '#ebf3ff', share: true }
  ] },
  { title: '合伙人管理', note: '独立站点', items: [
    { name: '店铺订单', icon: '订', color: '#3a3f4b', bg: '#f0f1f3', service: 'partner' },
    { name: '店铺收益', icon: '收', color: '#3a3f4b', bg: '#f0f1f3', service: 'partner' },
    { name: '发圈素材', icon: '圈', color: '#3a3f4b', bg: '#f0f1f3', service: 'partner' },
    { name: '店铺海报', icon: '报', color: '#3a3f4b', bg: '#f0f1f3', service: 'partner' }
  ] }
]

onShow(() => { if (memberStore.token) memberStore.getMemberInfo() })

const go = (url) => uni.navigateTo({ url })
const login = () => useLogin().setLoginBack({ url: '/addon/baijia_tools/pages/mine/index' })
const need = (url) => (memberStore.token ? go(url) : login())
const tap = (item) => {
  if (item.share) return
  if (item.service) openService(item.service)
  else need(item.url)
}

// Referral attribution belongs to the distribution module; this share is a plain invite.
onShareAppMessage(() => ({ title: '百迦好省 · 吃喝玩乐先省一笔', path: '/addon/baijia_tools/pages/home/index' }))
</script>

<style scoped>
@import "../../styles/base.css";
.profile { display: flex; align-items: center; padding: 20rpx 6rpx 30rpx; }
.avatar { width: 112rpx; height: 112rpx; border-radius: 50%; flex-shrink: 0; }
.avatar-empty { background: linear-gradient(135deg, #ff4c30, #ff9a3c); color: #fff; font-size: 48rpx; font-weight: 700; text-align: center; line-height: 112rpx; }
.profile-copy { flex: 1; padding-left: 24rpx; min-width: 0; }
.nickname { display: block; font-size: 36rpx; font-weight: 700; }
.profile-sub { display: block; font-size: 23rpx; color: #999; margin-top: 6rpx; }
.sign { padding: 0 28rpx; height: 60rpx; line-height: 60rpx; border-radius: 30rpx; background: #fff1eb; color: #ff5a2c; font-size: 25rpx; }
.stats { display: flex; padding: 24rpx 0; }
.stat { flex: 1; background: transparent; line-height: 1.4; }
.stat-num { display: block; font-size: 36rpx; font-weight: 700; }
.stat-name { display: block; font-size: 23rpx; color: #999; margin-top: 6rpx; }
.vip { display: flex; justify-content: space-between; align-items: center; margin-top: 20rpx; padding: 30rpx 28rpx; border-radius: 22rpx; background: linear-gradient(110deg, #2b2d33, #4a4c55); }
.vip-title { display: block; font-size: 32rpx; font-weight: 800; color: #f5d6a0; }
.vip-sub { display: block; font-size: 22rpx; color: #c9c2b3; margin-top: 6rpx; }
.vip-btn { flex-shrink: 0; padding: 12rpx 26rpx; border-radius: 30rpx; background: linear-gradient(90deg, #f8dfb0, #f0c37d); color: #4a3510; font-size: 25rpx; font-weight: 600; }
.group { margin-top: 20rpx; padding: 26rpx 12rpx 10rpx; }
.group-head { padding: 0 14rpx; }
.group-title { font-size: 31rpx; font-weight: 700; }
.group-note { font-size: 24rpx; color: #aaa; margin-left: 14rpx; }
.group-grid { display: flex; flex-wrap: wrap; margin-top: 12rpx; }
.cell { width: 25%; background: transparent; padding: 16rpx 0 20rpx; line-height: 1.4; display: flex; flex-direction: column; align-items: center; }
.cell-icon { width: 72rpx; height: 72rpx; border-radius: 22rpx; font-size: 32rpx; font-weight: 700; text-align: center; line-height: 72rpx; }
.cell-name { font-size: 24rpx; color: #43464d; margin-top: 10rpx; }
.footer-links { margin-top: 20rpx; padding: 0 24rpx; }
.link-row { display: flex; justify-content: space-between; background: transparent; padding: 26rpx 0; font-size: 27rpx; line-height: 1.5; text-align: left; }
.link-row + .link-row { border-top: 1rpx solid #f1f1f3; }
.arrow { color: #bbb; }
</style>
