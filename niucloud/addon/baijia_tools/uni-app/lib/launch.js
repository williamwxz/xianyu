// Runtime navigation for service entries (uses the app request helper, so it is
// kept apart from the pure, unit-tested services.mjs).
import request from '@/utils/request'
import { getService, isReady, resolveLink, servicePageUrl } from './services.mjs'

let actsPromise = null
// Official activity materials (icon/img) from 聚推客, keyed by jtk key.
export function loadActs() {
  if (!actsPromise) {
    actsPromise = request.get('baijia_tools/jtk/acts', {}, { showErrorMessage: false })
      .then((res) => res.data || {})
      .catch(() => { actsPromise = null; return {} })
  }
  return actsPromise
}

let opening = false
export async function openService(id) {
  const service = getService(id)
  if (!service || opening) return
  if (!service.jtk) {
    if (isReady(service) && service.launch.type === 'miniProgram') uni.navigateToMiniProgram({ appId: service.launch.appId, path: service.launch.path || '' })
    else if (isReady(service) && service.launch.type === 'page') uni.navigateTo({ url: service.launch.url })
    else uni.navigateTo({ url: servicePageUrl(id) })
    return
  }
  opening = true
  uni.showLoading({ title: '' })
  try {
    const res = await request.get('baijia_tools/jtk/link', { key: service.jtk })
    let canOpenMiniProgram = false
    // #ifdef MP-WEIXIN
    canOpenMiniProgram = true
    // #endif
    const target = resolveLink(res.data, canOpenMiniProgram)
    uni.hideLoading()
    if (!target) { uni.showToast({ title: '活动暂不可用', icon: 'none' }); return }
    if (target.type === 'miniProgram') {
      uni.navigateToMiniProgram({ appId: target.appId, path: target.path })
    } else if (target.type === 'copy') {
      uni.setClipboardData({
        data: target.url,
        success: () => uni.showModal({ title: '链接已复制', content: `请粘贴到浏览器打开「${service.name}」活动`, showCancel: false })
      })
    } else {
      // #ifdef H5
      window.location.href = target.url
      // #endif
    }
  } catch (e) {
    uni.hideLoading()
  } finally {
    opening = false
  }
}

let openingProduct = false
// Meituan product (from baijia_tools/meituan/products) -> converted promo link.
export async function openMeituanProduct(item) {
  if (openingProduct) return
  openingProduct = true
  uni.showLoading({ title: '正在打开' })
  try {
    const res = await request.get('baijia_tools/meituan/link', { id: item.id, platform: item.platform, biz_line: item.biz_line ?? '' })
    let mini = false
    // #ifdef MP-WEIXIN
    mini = true
    // #endif
    const target = resolveLink(res.data, mini)
    if (!target) throw new Error('empty link')
    if (target.type === 'miniProgram') {
      uni.navigateToMiniProgram({ appId: target.appId, path: target.path, fail: () => uni.showToast({ title: '未能打开美团，请重试', icon: 'none' }) })
    } else if (target.type === 'redirect') {
      // #ifdef H5
      window.location.href = target.url
      // #endif
    } else if (target.type === 'copy') { uni.setClipboardData({ data: target.url }) }
  } catch (e) { uni.showToast({ title: '商品暂不可购买，请稍后重试', icon: 'none' }) }
  finally { uni.hideLoading(); openingProduct = false }
}
