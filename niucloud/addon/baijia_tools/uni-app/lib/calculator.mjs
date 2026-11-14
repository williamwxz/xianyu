export const HISTORY_KEY = 'baijiahaosheng.tools.history.v1'
export const MODES = [
  { key: 'discount', label: '折扣计算' },
  { key: 'reduction', label: '满减优惠' },
  { key: 'coupon', label: '券后到手价' }
]

// Convert decimal input into integers before doing money arithmetic.
export function decimal(value, label, max, allowZero = false) {
  const raw = String(value ?? '').trim()
  if (!/^\d{1,7}(\.\d{1,2})?$/.test(raw)) throw new Error(`请填写${label}，最多保留两位小数`)
  const [whole, fraction = ''] = raw.split('.')
  const scaled = Number(whole) * 100 + Number(fraction.padEnd(2, '0'))
  if ((!allowZero && scaled === 0) || scaled > max * 100) {
    throw new Error(`${label}需${allowZero ? '不小于 0' : '大于 0'}，且不超过 ${max}`)
  }
  return scaled
}

export function money(cents) { return (cents / 100).toFixed(2) }

export function calculate(mode, fields) {
  if (!MODES.some(item => item.key === mode)) throw new Error('请选择计算方式')
  const original = decimal(fields.price, '商品金额', 999999)
  let final = original
  let note = ''
  let summary = ''
  if (mode === 'discount') {
    const rate = decimal(fields.discount, '折扣', 10)
    final = Math.round(original * rate / 1000)
    summary = `${rate / 100} 折`
    note = '商品金额 × 折扣 ÷ 10，四舍五入到分。'
  } else if (mode === 'reduction') {
    const threshold = decimal(fields.threshold, '满减门槛', 999999)
    const reduction = decimal(fields.reduction, '减免金额', 999999)
    if (reduction > threshold) throw new Error('减免金额不能大于满减门槛')
    const count = fields.repeat ? Math.floor(original / threshold) : Number(original >= threshold)
    final = original - count * reduction
    summary = `${fields.repeat ? '每' : ''}满 ${money(threshold)} 减 ${money(reduction)}`
    note = count ? `已达到门槛，优惠生效 ${count} 次。` : `还差 ¥${money(threshold - original)} 达到门槛；为优惠额外购买前，请先确认是否需要。`
  } else {
    const rate = decimal(fields.discount, '折扣', 10)
    const coupon = decimal(fields.coupon, '优惠券金额', 999999, true)
    const threshold = decimal(fields.couponThreshold, '用券门槛', 999999, true)
    const discounted = Math.round(original * rate / 1000)
    const eligible = discounted >= threshold
    const deduction = eligible ? Math.min(coupon, discounted) : 0
    final = discounted - deduction
    summary = `${rate / 100} 折后用 ¥${money(coupon)} 券`
    note = eligible
      ? `先打折，再用券；按折后金额判断门槛，实际抵扣 ¥${money(deduction)}。`
      : `折后 ¥${money(discounted)} 未达到 ¥${money(threshold)} 用券门槛，未抵扣优惠券。`
  }
  const saved = original - final
  return {
    mode, label: MODES.find(item => item.key === mode).label, summary, note,
    original, final, saved,
    originalText: money(original), finalText: money(final), savedText: money(saved),
    percentText: (saved / original * 100).toFixed(1),
    effectiveText: (final / original * 10).toFixed(2),
    fields: { ...fields }
  }
}

export function validHistory(value) {
  if (!Array.isArray(value)) return []
  return value.filter(item => {
    if (!item || !Number.isSafeInteger(item.id) || typeof item.date !== 'string') return false
    try { calculate(item.mode, item.fields); return true } catch { return false }
  }).slice(0, 20).map(item => ({ id: item.id, date: item.date, ...calculate(item.mode, item.fields) }))
}
