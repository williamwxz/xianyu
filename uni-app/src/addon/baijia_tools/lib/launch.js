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
