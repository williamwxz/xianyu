import request from '@/utils/request'

/**
 * 商户独立小程序：根据 AppID 识别商户并跳转店铺首页
 * 注意：此文件必须放在主包 utils 下，App.vue 启动时 addon 分包尚未加载
 */
export function redirectToMerchantStore() {
    // #ifdef MP-WEIXIN
    const cachedStoreId = uni.getStorageSync('current_store_id')
    if (cachedStoreId) {
        uni.reLaunch({
            url: `/addon/tk_group/pages/front/store_index?id=${cachedStoreId}`
        })
        return
    }

    try {
        const appId = uni.getAccountInfoSync?.()?.miniProgram?.appId
        if (!appId) return

        request.get('tk_group/getstorebyappid', { appid: appId }, { showErrorMessage: false }).then((res: any) => {
            if (res.data?.id) {
                uni.setStorageSync('current_store_id', res.data.id)
                uni.reLaunch({
                    url: `/addon/tk_group/pages/front/store_index?id=${res.data.id}`
                })
            }
        }).catch((err) => {
            console.log('商户小程序识别失败', err)
        })
    } catch (e) {
        console.log('商户小程序识别失败', e)
    }
    // #endif
}

/**
 * 获取当前微信小程序 AppID（商户独立小程序时为商户 AppID）
 */
export function getMerchantMpAppid(): string {
    // #ifdef MP-WEIXIN
    try {
        return uni.getAccountInfoSync?.()?.miniProgram?.appId || ''
    } catch (e) {
        return ''
    }
    // #endif
    return ''
}

/**
 * 微信登录/换绑 openid 时附带商户 AppID，供后端使用商户 AppSecret 换 code
 */
export function appendMerchantMpAppid(data: Record<string, any> = {}) {
    const mpAppid = getMerchantMpAppid()
    if (mpAppid) {
        data.mp_appid = mpAppid
    }
    return data
}
