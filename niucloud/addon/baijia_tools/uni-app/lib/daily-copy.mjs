const lines = [
  '今天想吃什么？搜搜附近的店',
  '午餐有着落了吗？看看门店菜单',
  '想换个口味？逛逛美团到店',
  '留点时间给电影，看看今日排片',
  '喝杯咖啡，搜搜喜欢的品牌',
  '晚餐去哪儿？看看同城团购',
  '去街区逛逛，发现一家新店'
]
const backups = ['看看附近有什么好吃的', '找找今天想去的店']
export function dailyCopy(timestamp = Date.now(), options = lines) {
  const day = Math.floor((timestamp + 8 * 3600 * 1000) / 86400000)
  const pool = options.length >= 4 ? options : backups
  return pool[(day % pool.length + pool.length) % pool.length]
}
