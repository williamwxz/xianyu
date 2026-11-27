// Service entries. `jtk` = key in the server-side 聚推客 mapping (JtkActDict.php);
// such entries open the partner activity directly. Entries without `jtk` and
// without `launch` show the "即将上线" page.
//   launch: { type: 'miniProgram', appId, path } | { type: 'webview', url } | { type: 'page', url }
// `img` = designed icon under static/design/ (惠省团 design, Moonvy).
export const SERVICES = [
  { id: 'bawangcan', jtk: 'bawangcan', name: '霸王餐', slogan: '下单返现 · 吃好不花钱', icon: '餐', color: '#ff5a2c', colorTo: '#ff9a3c', channel: '聚推客 · 美团霸王餐' },
  { id: 'movie', channel: '自营电影票', name: '电影票', slogan: '热映影片 · 特惠选座', icon: '影', color: '#ff3d6e', colorTo: '#ff7a9c' },
  { id: 'daodian', launch: { type: 'page', url: '/addon/baijia_tools/pages/catalog/index' }, name: '到店团购', slogan: '吃喝玩乐 · 团购1折起', icon: '团', color: '#13b87a', colorTo: '#46d39c', channel: '美团到店' },
  { id: 'waimai', jtk: 'waimai', name: '外卖红包', slogan: '美团外卖 · 每天领', icon: '红', color: '#ff7b1c', colorTo: '#ffb03a', channel: '聚推客 · 美团外卖' },
  { id: 'express', jtk: 'express', name: '寄快递', slogan: '特价寄件 · 上门取件', icon: '寄', color: '#7b5cf0', colorTo: '#a58bff', channel: '聚推客 · 特价快递' },
  { id: 'taxi', jtk: 'taxi', name: '打车', slogan: '打车出行 · 领券更省', icon: '车', color: '#ff8a00', colorTo: '#ffb84d', channel: '聚推客 · 打车出行' },
  { id: 'hotel', jtk: 'hotel', name: '酒店', slogan: '特惠订房 · 出行无忧', icon: '宿', color: '#2f7cf6', colorTo: '#5fb0ff', channel: '聚推客 · 美团酒店' },
  { id: 'goods', jtk: 'goods', name: '领券购物', slogan: '大牌好物 · 领券再买', icon: '券', color: '#e8384f', colorTo: '#ff6b6b', channel: '聚推客 · 拼多多' },
  { id: 'carwash', name: '洗车服务', slogan: '洁净焕新 · 一路畅行', icon: '洗', color: '#2f7cf6', colorTo: '#5fb0ff', channel: '洗车服务商（需商务洽谈）' },
  { id: 'fuel', name: '加油服务', slogan: '附近油站 · 直降优惠', icon: '油', color: '#12a36b', colorTo: '#3ccf8e', channel: '加油服务商（需商务洽谈）' },
  { id: 'distribution', name: '推广中心', slogan: '邀请好友 · 共享收益', icon: '推', color: '#f0a020', colorTo: '#ffc94d', launch: { type: 'page', url: '/addon/baijia_tools/pages/promotion/index' } },
  { id: 'partner', name: '合伙人', slogan: '独立站点 · 自主经营', icon: '合', color: '#3a3f4b', colorTo: '#6a7080', channel: '合伙人模块（开发中）' },
  { id: 'ai', name: 'AI助手', slogan: '问问附近有什么好吃好玩', icon: 'AI', color: '#202120', colorTo: '#4a4c48', channel: 'AI 助手（开发中）' },
  { id: 'mayi', name: '蚂蚁星球', slogan: '话费电费 · 美团券包', icon: '惠', launch: { type: 'page', url: '/addon/baijia_tools/pages/mayi/index' } },
  { id: 'jiazheng', name: '家政服务', slogan: '保洁维修 · 上门服务', icon: '家', img: 'grid/jiazheng.png', color: '#2f7cf6', colorTo: '#5fb0ff', channel: '家政服务商（需商务洽谈）' },
  // 大牌点餐
  { id: 'mcdonalds', brand: true, name: '麦当劳', icon: '麦', img: 'grid/mcdonalds.png', color: '#da291c', colorTo: '#ffc72c', channel: '麦当劳点餐（待接入）' },
  { id: 'kfc', channel: '品牌点餐', brand: true, img: 'grid/kfc.png', name: '肯德基', icon: 'K', color: '#e4002b', colorTo: '#ff4d6a', tag: '在线点餐' },
  { id: 'pizzahut', channel: '品牌点餐', brand: true, img: 'grid/pizzahut.png', name: '必胜客', icon: '必', color: '#d0021b', colorTo: '#ff5050' },
  { id: 'starbucks', channel: '品牌点餐', brand: true, img: 'grid/starbucks.png', name: '星巴克', icon: '星', color: '#00704a', colorTo: '#2fa173' },
  { id: 'luckin', channel: '品牌点餐', brand: true, img: 'grid/luckin.png', name: '瑞幸咖啡', icon: '瑞', color: '#1a3b8f', colorTo: '#4a6fd0', tag: '低价' },
  { id: 'cotti', channel: '品牌点餐', brand: true, img: 'grid/cotti.png', name: '库迪咖啡', icon: '库', color: '#8a1538', colorTo: '#c0466c' },
  { id: 'nayuki', channel: '品牌点餐', brand: true, img: 'grid/nayuki.png', name: '奈雪的茶', icon: '奈', color: '#5aa83c', colorTo: '#8bd06b' },
  { id: 'heytea', channel: '品牌点餐', brand: true, name: '喜茶', icon: '喜', color: '#333333', colorTo: '#666666' },
  { id: 'burgerking', channel: '品牌点餐', brand: true, name: '汉堡王', icon: '汉', color: '#d2691e', colorTo: '#f29b4b' },
  { id: 'pagoda', channel: '品牌点餐', brand: true, name: '百果园', icon: '果', color: '#4caf50', colorTo: '#81d97f' },
  { id: 'eleme', jtk: 'eleme', brand: true, img: 'grid/shangou.png', name: '淘宝闪购', icon: '闪', color: '#0089ff', colorTo: '#4db1ff', tag: '红包' }
]

for (const service of SERVICES.filter(item => item.brand && !item.jtk)) {
  service.launch = { type: 'page', url: '/addon/baijia_tools/pages/dining/index?brand=' + service.id }
}

// Home grid follows the design; 「更多」 expands MORE_GRID.
export const HOME_GRID = ['mcdonalds', 'kfc', 'starbucks', 'cotti', 'nayuki', 'pizzahut', 'luckin', 'eleme', 'jiazheng']
export const MORE_GRID = ['mayi', 'daodian', 'waimai', 'express', 'taxi', 'hotel', 'goods', 'heytea', 'burgerking', 'pagoda', 'carwash', 'fuel']
export const BRANDS = SERVICES.filter((item) => item.brand).map((item) => item.id)

export function getService(id) {
  return SERVICES.find((item) => item.id === id) || null
}

export function isReady(service) {
  const launch = service && service.launch
  if (!launch) return false
  if (launch.type === 'miniProgram') return Boolean(launch.appId)
  if (launch.type === 'webview' || launch.type === 'page') return Boolean(launch.url)
  return false
}

// Turn a server link result ({ weapp, h5 }) into how this client can open it.
export function resolveLink(link, canOpenMiniProgram) {
  if (!link) return null
  if (canOpenMiniProgram && link.weapp && link.weapp.app_id) return { type: 'miniProgram', appId: link.weapp.app_id, path: link.weapp.page_path || '' }
  if (link.h5) return canOpenMiniProgram ? { type: 'copy', url: link.h5 } : { type: 'redirect', url: link.h5 }
  return null
}

// Keep explicit brands ahead of broad categories in conversational searches.
export function productKeyword(query) {
  const text = String(query || '').trim()
  const words = text.replace(/^(?:帮我找找|帮我找|附近有没有|附近的|想点|我想吃|我想喝|我想看|我想买)/, '').trim() || text
  const alias = SERVICES.find(item => item.brand && words.toLowerCase().includes(item.id))
  if (!alias) return words
  const index = words.toLowerCase().indexOf(alias.id)
  return words.slice(0, index) + alias.name + words.slice(index + alias.id.length)
}

export function searchServices(keyword) {
  const words = String(keyword || '').trim().toLowerCase()
  if (!words) return []
  return SERVICES.filter((item) => `${item.name}${item.slogan || ''}`.toLowerCase().includes(words))
}

export function servicePageUrl(id) {
  return `/addon/baijia_tools/pages/service/index?id=${encodeURIComponent(id)}`
}
