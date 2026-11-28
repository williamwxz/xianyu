// Short locally labelled examples keep user searches on the device.
// Compare character unigrams and bigrams across intents, including unseen phrases.
const examples = {
  movie: ['想看电影', '今晚看场电影', '买电影票', '影院排片', '电影院场次', '去看热映大片', '看什么片', '选座买票'],
  kfc: ['去肯德基', '想吃肯德基', '肯德基点餐', '肯德基蛋挞', '疯狂星期四', '吮指原味鸡', 'KFC自取'],
  waimai: ['点份外卖', '叫外卖到家', '想领外卖红包', '点餐送到家'],
  mayi: ['给手机充话费', '缴电费', '充值话费', '想领美团券包'],
  daodian: ['附近有什么好吃的', '周末附近探店', '去店里吃饭', '到店团购', '找附近的餐厅'],
  express: ['想寄个快递', '寄包裹', '上门取件', '寄件多少钱']
}
function features(value) {
  const chars = Array.from(value.toLowerCase().replace(/[\s，。？！]/g, ''))
  return new Set(chars.flatMap((ch, i) => i ? [ch, chars[i - 1] + ch] : [ch]))
}
const corpus = Object.entries(examples).flatMap(([id, phrases]) => phrases.map(phrase => ({ id, terms: features(phrase) })))
export function inferService(query) {
  const words = features(String(query || ''))
  if (!words.size) return ''
  let best = ''
  let bestScore = 0.36
  for (const { id, terms } of corpus) {
    let common = 0
    let pairs = 0
    for (const term of terms) if (words.has(term)) {
      common++
      if (term.length > 1) pairs++
    }
    const score = common / Math.sqrt(words.size * terms.size)
    if (pairs && score > bestScore) { bestScore = score; best = id }
  }
  return best
}
