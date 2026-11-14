export const ARTICLES = [
  { id: 'discount', tag: '折扣速查', symbol: '折', color: '#ff703f', title: '8.5 折，是便宜了多少？', brief: '看懂折扣，比较价格更直观。', paragraphs: ['折扣表示实际支付的比例。8.5 折就是支付原价的 85%，便宜了 15%；5 折是半价。', '例如原价 199 元，8.5 折到手价为 199 × 0.85 = 169.15 元，节省 29.85 元。', '比较两个商品时，先看相同规格的实际到手价。折扣更低，不一定代表最终支付金额更少。'], mode: 'discount' },
  { id: 'threshold', tag: '满减指南', symbol: '减', color: '#ff4380', title: '凑单之前，先算这笔账', brief: '达到门槛，不等于花得更少。', paragraphs: ['满 200 减 30，买 199 元时无法享受优惠。如果加入一件真正需要的 1 元商品，到手价可以变成 170 元。', '但如果凑单需要多买 50 元商品，最终支付 219 元，反而比原来多花 20 元。先确认新增商品是否需要，再比较总支出。', '“满减一次”与“每满减”不同。650 元满 200 减 30，只减一次是 620 元；每满减则生效三次，到手价 560 元。'], mode: 'reduction' },
  { id: 'coupon', tag: '叠券指南', symbol: '券', color: '#9868ed', title: '先打折，还是先用券？', brief: '同样的券，叠加顺序会影响结果。', paragraphs: ['200 元商品打 8 折，再使用 20 元优惠券，到手价为 140 元。先减去 20 元再打 8 折，则为 144 元。', '本工具的“券后到手价”采用先打折、再用券的顺序，并按折后金额判断用券门槛。请以商家实际结算规则为准。', '注意看优惠券的有效期、适用品类、门槛和可叠加条件。计算结果不包含运费、积分、返现或商家的特殊限制。'], mode: 'coupon' }
]

export const ENTRIES = [
  { id:'discount', name:'折扣计算', icon:'折', color:'#ff493d', badge:'常用', mode:'discount' },
  { id:'reduction', name:'满减优惠', icon:'减', color:'#ff831d', mode:'reduction' },
  { id:'coupon', name:'券后价', icon:'券', color:'#ff487e', badge:'叠加', mode:'coupon' },
  { id:'repeat', name:'每满减', icon:'满', color:'#20b97b', mode:'reduction', repeat:true },
  { id:'nocondition', name:'无门槛券', icon:'¥', color:'#9a63e7', mode:'coupon', noThreshold:true },
  { id:'threshold-guide', name:'凑单指南', icon:'单', color:'#1cbcc4', article:'threshold' },
  { id:'coupon-guide', name:'叠券指南', icon:'叠', color:'#e63b5d', article:'coupon' },
  { id:'discount-guide', name:'折扣速查', icon:'%', color:'#4c8cfa', article:'discount' },
  { id:'history', name:'计算记录', icon:'记', color:'#ff9360', profile:true },
  { id:'help', name:'使用帮助', icon:'?', color:'#8570d9', help:true }
]

// The next tab receives a short-lived selection; no extra persistent user data.
let pendingCalculation = null
export function openCalculator(selection = {}) {
  pendingCalculation = selection
  uni.switchTab({ url: '/pages/calculator/index' })
}
export function takeCalculation() {
  const value = pendingCalculation
  pendingCalculation = null
  return value
}
export function openArticle(id) { uni.navigateTo({ url: `/pages/article/index?id=${encodeURIComponent(id)}` }) }
export function openEntry(item) {
  if (item.mode) openCalculator(item)
  else if (item.article) openArticle(item.article)
  else if (item.profile) uni.switchTab({url:'/pages/profile/index'})
  else if (item.help) uni.navigateTo({url:'/pages/about/index'})
}
