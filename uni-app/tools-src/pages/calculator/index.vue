<template>
  <view class="shell">
    <view class="eyebrow"><view class="brand-dot" /> 百迦好省 · 省钱小工具</view>
    <view class="intro"><view class="title"><view>优惠怎么算，</view><view>心里有个数。</view></view><view class="stamp">省<text>得明白</text></view></view>
    <text class="subtitle">折扣、满减、用券，帮你算清到手价。</text>

    <view class="tabs">
      <button v-for="item in MODES" :key="item.key" class="tab" :class="{ selected: mode === item.key }" @click="switchMode(item.key)">{{ item.label }}</button>
    </view>

    <view class="panel">
      <view class="panel-heading"><text>{{ currentLabel }}</text><text class="step">01 / 填写金额</text></view>
      <view class="field">
        <label for="price">商品金额</label>
        <view class="input-wrap"><text class="prefix">¥</text><input id="price" v-model="fields.price" type="digit" maxlength="10" placeholder="输入优惠前的总金额" placeholder-class="placeholder" aria-label="商品金额" /></view>
      </view>

      <template v-if="mode !== 'reduction'">
        <view class="field">
          <label for="discount">商品折扣<text class="label-note">10 折即原价</text></label>
          <view class="input-wrap"><input id="discount" v-model="fields.discount" type="digit" maxlength="5" placeholder="例如 8.5" placeholder-class="placeholder" aria-label="商品折扣" /><text class="unit">折</text></view>
        </view>
        <view class="presets"><text class="preset-label">常用</text><button v-for="value in ['9', '8.5', '8', '5']" :key="value" class="chip" :class="{ active: fields.discount === value }" @click="fields.discount = value">{{ value }} 折</button></view>
      </template>

      <template v-if="mode === 'reduction'">
        <view class="field-row">
          <view class="field"><label for="threshold">满多少</label><view class="input-wrap"><text class="prefix">¥</text><input id="threshold" v-model="fields.threshold" type="digit" maxlength="10" placeholder="例如 200" aria-label="满减门槛" /></view></view>
          <view class="field"><label for="reduction">减多少</label><view class="input-wrap"><text class="prefix">¥</text><input id="reduction" v-model="fields.reduction" type="digit" maxlength="10" placeholder="例如 30" aria-label="减免金额" /></view></view>
        </view>
        <view class="repeat"><view><text>每满减，可重复享受</text><text class="small">关闭时，达到门槛只减一次</text></view><switch :checked="fields.repeat" color="#ff633b" aria-label="每满减" @change="fields.repeat = $event.detail.value" /></view>
      </template>

      <template v-if="mode === 'coupon'">
        <view class="field-row">
          <view class="field"><label for="coupon">优惠券金额</label><view class="input-wrap"><text class="prefix">¥</text><input id="coupon" v-model="fields.coupon" type="digit" maxlength="10" placeholder="例如 20" aria-label="优惠券金额" /></view></view>
          <view class="field"><label for="couponThreshold">用券门槛</label><view class="input-wrap"><text class="prefix">¥</text><input id="couponThreshold" v-model="fields.couponThreshold" type="digit" maxlength="10" placeholder="无门槛填 0" aria-label="用券门槛" /></view></view>
        </view>
        <view class="rule">先打折，再用券。用券门槛按折后金额判断。</view>
      </template>

      <view v-if="error" class="error" role="alert">{{ error }}</view>
      <button class="primary" @click="runCalculation">算算到手价 <text class="arrow">↗</text></button>
      <view class="secondary-actions"><button @click="useExample">试算一个例子</button><button @click="reset">清空</button></view>
    </view>

    <view v-if="result" id="result" class="result-card">
      <view class="result-heading"><text>预计到手价</text><text class="result-tag">省下 {{ result.percentText }}%</text></view>
      <view class="total"><text class="currency">¥</text>{{ result.finalText }}</view>
      <view class="result-divider" />
      <view class="breakdown"><view><text class="metric-label">优惠前</text><text>¥{{ result.originalText }}</text></view><view><text class="metric-label">共省下</text><text class="saved">¥{{ result.savedText }}</text></view><view><text class="metric-label">相当于</text><text>{{ result.effectiveText }} 折</text></view></view>
      <text class="result-note">{{ result.note }}</text>
      <button class="save" @click="saveResult">{{ saved ? '已保存到本机' : '保存本次计算' }}</button>
    </view>

    <view v-if="!result" class="tip"><text class="tip-symbol">＝</text><view><text class="tip-title">少一点心算，多一点明白</text><text class="small">金额精确到分，算完再决定怎么买。</text></view></view>
    <text class="disclaimer">计算结果仅作价格比较，不含运费；实际优惠以商家结算规则为准。</text>

    <view class="history-heading"><text>最近计算 <text class="count">{{ history.length }}</text></text><button v-if="history.length" class="text-button" @click="clearHistory">清空记录</button></view>
    <view v-if="!history.length" class="empty"><text>还没有保存的计算</text><text class="small">点击“保存本次计算”后可在这里回看。</text></view>
    <button v-for="item in history" :key="item.id" class="history-item" @click="restore(item)"><view class="history-left"><text class="history-title">{{ item.label }}</text><text class="history-detail">{{ item.summary }}</text><text class="date">{{ item.date }}</text></view><view class="history-right"><text>¥{{ item.finalText }}</text><text class="small">省 ¥{{ item.savedText }} ›</text></view></button>

    <view class="footer"><text>本地计算 · 无需登录</text><button @click="openAbout">使用说明与隐私 ›</button></view>
  </view>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { MODES, calculate, validHistory, HISTORY_KEY } from '../../lib/calculator.mjs'
import { takeCalculation } from '../../lib/content.mjs'

const STORAGE_KEY = HISTORY_KEY
const blank = () => ({ price: '', discount: '', threshold: '', reduction: '', repeat: false, coupon: '', couponThreshold: '0' })
const mode = ref('discount')
const fields = ref(blank())
const result = ref(null)
const error = ref('')
const saved = ref(false)
const history = ref([])
const currentLabel = computed(() => MODES.find(item => item.key === mode.value).label)

watch(fields, () => { result.value = null; error.value = ''; saved.value = false }, { deep: true, flush: 'sync' })
onShow(() => {
  try { history.value = validHistory(uni.getStorageSync(STORAGE_KEY)) }
  catch { uni.showToast({ title: '本地记录暂时无法读取，仍可计算', icon: 'none' }) }
  const selection = takeCalculation()
  if (!selection) return
  if (selection.record) { restore(selection.record); return }
  if (selection.mode && MODES.some(item => item.key === selection.mode)) switchMode(selection.mode)
  if (selection.repeat) fields.value.repeat = true
  if (selection.noThreshold) { fields.value.discount = '10'; fields.value.couponThreshold = '0' }
  if (selection.example) useExample()
})
function switchMode(value) { mode.value = value; reset() }
function reset() { fields.value = blank(); result.value = null; error.value = ''; saved.value = false }
function useExample() {
  fields.value = mode.value === 'discount' ? { ...blank(), price: '199', discount: '8.5' }
    : mode.value === 'reduction' ? { ...blank(), price: '650', threshold: '200', reduction: '30', repeat: true }
    : { ...blank(), price: '200', discount: '8', coupon: '20', couponThreshold: '150' }
  runCalculation()
}
function runCalculation() {
  try { result.value = calculate(mode.value, fields.value); error.value = ''; saved.value = false; uni.hideKeyboard() }
  catch (e) { result.value = null; error.value = e.message }
}
function saveResult() {
  if (!result.value || saved.value) return
  const now = new Date()
  const two = value => String(value).padStart(2, '0')
  const item = { ...result.value, id: Date.now(), date: `${now.getFullYear()}/${two(now.getMonth()+1)}/${two(now.getDate())} ${two(now.getHours())}:${two(now.getMinutes())}` }
  const next = [item, ...history.value].slice(0, 20)
  try { uni.setStorageSync(STORAGE_KEY, next); history.value = next; saved.value = true }
  catch { uni.showToast({ title: '保存失败，请检查手机存储空间', icon: 'none' }) }
}
function restore(item) {
  mode.value = item.mode; fields.value = { ...item.fields }; runCalculation(); saved.value = true
  uni.pageScrollTo({ scrollTop: 0, duration: 250 })
}
function clearHistory() {
  uni.showModal({ title: '清空本机计算记录？', content: '清空后无法恢复，不影响继续计算。', cancelText: '取消', confirmText: '清空记录', confirmColor: '#ff633b', success: response => {
    if (!response.confirm) return
    try { uni.removeStorageSync(STORAGE_KEY); history.value = []; saved.value = false }
    catch { uni.showToast({ title: '清空失败，请稍后重试', icon: 'none' }) }
  } })
}
function openAbout() { uni.navigateTo({ url: '/pages/about/index' }) }
</script>

<style scoped>
.shell { max-width: 960rpx; margin: 0 auto; padding: 40rpx 32rpx calc(32rpx + env(safe-area-inset-bottom)); }
.eyebrow { display: flex; align-items: center; font-size: 22rpx; color: #b28779; letter-spacing: 2rpx; }
.brand-dot { width: 12rpx; height: 12rpx; border-radius: 50%; background: #ff5639; margin-right: 14rpx; }
.intro { display: flex; justify-content: space-between; align-items: center; margin-top: 22rpx; }
.title { font-size: 52rpx; font-weight: 700; line-height: 1.35; letter-spacing: -2rpx; white-space: pre-line; }
.stamp { width: 110rpx; height: 110rpx; background: #ffe6d2; border-radius: 50%; display: flex; flex-direction: column; justify-content: center; align-items: center; font-size: 42rpx; font-weight: 600; transform: rotate(10deg); }
.stamp text { font-size: 18rpx; margin-top: -2rpx; }
.subtitle { display: block; margin-top: 20rpx; color: #999999; font-size: 25rpx; }
.tabs { display: flex; gap: 6rpx; margin: 38rpx 0 22rpx; padding: 8rpx; border: 1rpx solid #eeeeee; border-radius: 20rpx; background: #f1f2f4; }
.tab { flex: 1; padding: 18rpx 4rpx; background: transparent; color: #888888; font-size: 25rpx; line-height: 1.4; border-radius: 14rpx; }
.tab.selected { color: #fff; background: #ff5838; font-weight: 600; box-shadow: 0 4rpx 10rpx #ff583820; }
.panel { padding: 30rpx; background: #fff; border-radius: 26rpx; border: 1rpx solid #eeeeee; }
.panel-heading { display: flex; align-items: center; justify-content: space-between; font-size: 29rpx; font-weight: 600; margin-bottom: 26rpx; }
.step { font-size: 18rpx; color: #aaaaaa; font-weight: 400; letter-spacing: 1rpx; }
.field { margin-bottom: 24rpx; flex: 1; min-width: 0; }
label { display: block; font-size: 24rpx; margin-bottom: 13rpx; color: #555555; }
.label-note { margin-left: 18rpx; font-size: 21rpx; color: #aaaaaa; }
.input-wrap { display: flex; align-items: center; height: 90rpx; padding: 0 22rpx; background: #fafafa; border: 1rpx solid #eeeeee; border-radius: 14rpx; }
input { flex: 1; width: 0; min-width: 0; height: 80rpx; font-size: 32rpx; color: #383838; }
.placeholder, input::placeholder { font-size: 25rpx; color: #bbbbbb; }
.prefix { margin-right: 18rpx; color: #aaaaaa; font-size: 30rpx; }
.unit { margin-left: 12rpx; color: #aaaaaa; font-size: 25rpx; }
.presets { display: flex; align-items: center; gap: 12rpx; margin: -6rpx 0 26rpx; }
.preset-label { font-size: 21rpx; color: #aaaaaa; margin-right: 5rpx; }
.chip { background: #fff4ec; color: #bb8d76; font-size: 22rpx; line-height: 1.4; padding: 10rpx 18rpx; border: 1rpx solid transparent; border-radius: 10rpx; }
.chip.active { border-color: #ffad8d; color: #ff5a36; background: #fff0e7; }
.field-row { display: flex; gap: 20rpx; }
.repeat { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28rpx; font-size: 24rpx; }
.small { display: block; font-size: 22rpx; line-height: 1.6; color: #999999; margin-top: 6rpx; }
.rule { font-size: 22rpx; line-height: 1.6; background: #fff5ed; color: #ac8574; padding: 16rpx; margin-bottom: 24rpx; border-radius: 12rpx; }
.primary { background: #ff5838; color: white; font-size: 29rpx; font-weight: 600; border-radius: 14rpx; line-height: 1.5; padding: 22rpx 24rpx; text-align: left; display: flex; justify-content: space-between; }
.arrow { color: #ffe6d2; font-size: 32rpx; }
.secondary-actions { display: flex; justify-content: space-between; margin-top: 20rpx; }
.secondary-actions button, .text-button { padding: 0; color: #b98d78; background: transparent; font-size: 23rpx; line-height: 1.8; }
.error { color: #a34533; background: #fff4ef; padding: 16rpx; border-radius: 10rpx; font-size: 24rpx; margin-bottom: 20rpx; }
.result-card { background: #fff0e4; border: 1rpx solid #ffdcc5; border-radius: 26rpx; padding: 30rpx; margin-top: 22rpx; }
.result-heading { display: flex; align-items: center; justify-content: space-between; color: #ad765e; font-size: 25rpx; }
.result-tag { font-size: 21rpx; background: #fffaf5; padding: 8rpx 16rpx; border-radius: 30rpx; }
.total { font-size: 76rpx; font-weight: 700; letter-spacing: -3rpx; padding: 10rpx 0 20rpx; color: #f15331; font-variant-numeric: tabular-nums; word-break: break-all; }
.currency { font-size: 34rpx; margin-right: 10rpx; font-weight: 400; }
.result-divider { border-top: 1rpx dashed #efcfb9; }
.breakdown { display: flex; justify-content: space-between; padding: 24rpx 0; font-size: 27rpx; }
.breakdown > view { flex: 1; min-width: 0; }
.metric-label { display: block; color: #b29383; font-size: 21rpx; margin-bottom: 8rpx; }
.saved { color: #ff633b; font-weight: 600; }
.result-note { display: block; font-size: 22rpx; color: #a48473; line-height: 1.6; }
.save { width: 100%; font-size: 24rpx; background: transparent; border: 1rpx solid #e6b89a; border-radius: 12rpx; color: #c77850; margin-top: 24rpx; line-height: 1.5; padding: 15rpx; }
.tip { display: flex; align-items: center; gap: 20rpx; margin-top: 28rpx; padding: 26rpx; border: 1rpx dashed #edd8c8; border-radius: 20rpx; }
.tip-symbol { color: #d99f7b; font-size: 40rpx; }
.tip-title { font-size: 25rpx; color: #9b7e69; }
.disclaimer { display: block; color: #aaaaaa; font-size: 21rpx; line-height: 1.6; margin: 20rpx 4rpx 38rpx; }
.history-heading { display: flex; justify-content: space-between; align-items: center; font-size: 28rpx; font-weight: 600; margin-bottom: 18rpx; }
.count { font-size: 20rpx; color: #bbbbbb; font-weight: 400; margin-left: 8rpx; }
.empty { padding: 30rpx; background: #fff8f2; border-radius: 18rpx; font-size: 24rpx; color: #ae9b8c; }
.history-item { text-align: left; display: flex; justify-content: space-between; align-items: center; padding: 24rpx; margin-bottom: 12rpx; background: white; border-radius: 16rpx; line-height: 1.5; }
.history-left { flex: 1; min-width: 0; padding-right: 16rpx; }
.history-title { font-size: 25rpx; color: #555555; display: block; }
.history-detail { font-size: 22rpx; color: #999999; display: block; }
.date { display: block; color: #bbbbbb; font-size: 19rpx; margin-top: 5rpx; }
.history-right { text-align: right; color: #ff633b; font-size: 29rpx; font-weight: 600; }
.history-right .small { font-weight: 400; font-size: 20rpx; }
.footer { display: flex; flex-direction: column; align-items: center; gap: 8rpx; margin-top: 40rpx; font-size: 21rpx; color: #bbbbbb; }
.footer button { font-size: 22rpx; background: transparent; color: #bd9279; padding: 8rpx; line-height: 1.5; }
</style>
