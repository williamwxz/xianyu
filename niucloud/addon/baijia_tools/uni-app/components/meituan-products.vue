<template>
  <view class="products">
    <button v-for="item in items" :key="item.id" class="product" @click="$emit('open', item)">
      <image v-if="item.image" :src="item.image" mode="aspectFill" class="photo" />
      <view v-else class="photo placeholder">团购</view>
      <view class="info">
        <text class="name">{{ item.name }}</text>
        <view class="prices"><text class="price">¥{{ item.price }}</text><text v-if="Number(item.original_price) > Number(item.price)" class="original">¥{{ item.original_price }}</text></view>
        <text v-if="item.sales" class="meta">美团联盟销量：{{ item.sales }}</text>
        <text v-if="item.stores !== null" class="meta">所选城市 {{ item.stores }} 家可用门店</text>
        <text class="buy">去美团购买 ›</text>
      </view>
    </button>
  </view>
</template>
<script setup>
defineProps({ items: { type: Array, default: () => [] } })
defineEmits(['open'])
</script>
<style scoped>
.product { display:flex; gap:20rpx; background:#fff; padding:24rpx 0; text-align:left; border-bottom:1rpx solid #f1f2f4; border-radius:0; line-height:1.5; }
.product::after { border:0; }
.photo { width:180rpx; height:180rpx; flex-shrink:0; border-radius:16rpx; background:#f3f5f4; }
.placeholder { display:flex; align-items:center; justify-content:center; color:#999; }
.info { min-width:0; flex:1; }
.name { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; font-size:27rpx; font-weight:600; }
.prices { margin:8rpx 0; }.price { font-size:34rpx; color:#f35428; font-weight:700; }.original { font-size:23rpx; color:#999; text-decoration:line-through; margin-left:12rpx; }
.meta { display:block; font-size:21rpx; color:#888; }.buy { display:block; margin-top:6rpx; color:#159c6a; font-size:24rpx; }
</style>
