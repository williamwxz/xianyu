import request from '@/addon/tk_upgrade/utils/request'


/***************************************************** 升级助手接口 ****************************************************/
export function addonUpload(params: Record<string, any>) {
    return request.post(`tk_upgrade/addonupload`, params, { showSuccessMessage: true, showErrorMessage: true })
}
export function checkAuthInfo() {
    return request.get(`tk_upgrade/checkauthinfo`)
}
