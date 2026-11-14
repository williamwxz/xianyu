// Service entries. `jtk` = key in the server-side 聚推客 mapping (JtkActDict.php);
// such entries open the partner activity directly. Entries without `jtk` and
// without `launch` show the "即将上线" page.
//   launch: { type: 'miniProgram', appId, path } | { type: 'webview', url } | { type: 'page', url }
export const SERVICES = [
  { id: 'bawangcan', jtk: 'bawangcan', name: '霸王餐', slogan: '下单返现 · 吃好不花钱', icon: '餐', color: '#ff5a2c', colorTo: '#ff9a3c', channel: '聚推客 · 美团霸王餐' },
  { id: 'movie', jtk: 'movie', name: '电影票', slogan: '热映影片 · 特惠选座', icon: '影', color: '#ff3d6e', colorTo: '#ff7a9c', channel: '聚推客 · 电影票' },
  { id: 'daodian', jtk: 'daodian', name: '到店团购', slogan: '吃喝玩乐 · 团购1折起', icon: '团', color: '#13b87a', colorTo: '#46d39c', channel: '聚推客 · 美团团购' },
  { id: 'waimai', jtk: 'waimai', name: '外卖红包', slogan: '美团外卖 · 每天领', icon: '红', color: '#ff7b1c', colorTo: '#ffb03a', channel: '聚推客 · 美团外卖' },
  { id: 'express', jtk: 'express', name: '寄快递', slogan: '特价寄件 · 上门取件', icon: '寄', color: '#7b5cf0', colorTo: '#a58bff', channel: '聚推客 · 特价快递' },
  { id: 'taxi', jtk: 'taxi', name: '打车', slogan: '打车出行 · 领券更省', icon: '车', color: '#ff8a00', colorTo: '#ffb84d', channel: '聚推客 · 打车出行' },
  { id: 'hotel', jtk: 'hotel', name: '酒店', slogan: '特惠订房 · 出行无忧', icon: '宿', color: '#2f7cf6', colorTo: '#5fb0ff', channel: '聚推客 · 美团酒店' },
  { id: 'goods', jtk: 'goods', name: '领券购物', slogan: '大牌好物 · 领券再买', icon: '券', color: '#e8384f', colorTo: '#ff6b6b', channel: '聚推客 · 拼多多' },
  { id: 'carwash', name: '洗车服务', slogan: '洁净焕新 · 一路畅行', icon: '洗', color: '#2f7cf6', colorTo: '#5fb0ff', channel: '洗车服务商（需商务洽谈）' },
  { id: 'fuel', name: '加油服务', slogan: '附近油站 · 直降优惠', icon: '油', color: '#12a36b', colorTo: '#3ccf8e', channel: '加油服务商（需商务洽谈）' },
  { id: 'distribution', name: '推广中心', slogan: '邀请好友 · 共享收益', icon: '推', color: '#f0a020', colorTo: '#ffc94d', channel: '分销模块（开发中）' },
  { id: 'partner', name: '合伙人', slogan: '独立站点 · 自主经营', icon: '合', color: '#3a3f4b', colorTo: '#6a7080', channel: '合伙人模块（开发中）' },
  // 大牌点餐
  { id: 'kfc', jtk: 'kfc', brand: true, name: '肯德基', icon: 'K', color: '#e4002b', colorTo: '#ff4d6a', tag: '在线点餐' },
  { id: 'pizzahut', jtk: 'pizzahut', brand: true, name: '必胜客', icon: '必', color: '#d0021b', colorTo: '#ff5050' },
  { id: 'starbucks', jtk: 'starbucks', brand: true, name: '星巴克', icon: '星', color: '#00704a', colorTo: '#2fa173' },
  { id: 'luckin', jtk: 'luckin', brand: true, name: '瑞幸咖啡', icon: '瑞', color: '#1a3b8f', colorTo: '#4a6fd0', tag: '低价' },
  { id: 'cotti', jtk: 'cotti', brand: true, name: '库迪咖啡', icon: '库', color: '#8a1538', colorTo: '#c0466c' },
  { id: 'nayuki', jtk: 'nayuki', brand: true, name: '奈雪的茶', icon: '奈', color: '#5aa83c', colorTo: '#8bd06b' },
  { id: 'heytea', jtk: 'heytea', brand: true, name: '喜茶', icon: '喜', color: '#333333', colorTo: '#666666' },
  { id: 'burgerking', jtk: 'burgerking', brand: true, name: '汉堡王', icon: '汉', color: '#d2691e', colorTo: '#f29b4b' },
  { id: 'pagoda', jtk: 'pagoda', brand: true, name: '百果园', icon: '果', color: '#4caf50', colorTo: '#81d97f' },
  { id: 'eleme', jtk: 'eleme', brand: true, name: '饿了么', icon: '饿', color: '#0089ff', colorTo: '#4db1ff', tag: '红包' }
]

export const HOME_GRID = ['bawangcan', 'movie', 'daodian', 'waimai', 'express', 'taxi', 'hotel', 'goods']
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

export function searchServices(keyword) {
  const words = String(keyword || '').trim().toLowerCase()
  if (!words) return []
  return SERVICES.filter((item) => `${item.name}${item.slogan || ''}`.toLowerCase().includes(words))
}

export function servicePageUrl(id) {
  return `/addon/baijia_tools/pages/service/index?id=${encodeURIComponent(id)}`
}
