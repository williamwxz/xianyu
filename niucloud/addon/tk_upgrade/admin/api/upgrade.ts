import request from '@/addon/tk_upgrade/utils/request'


/***************************************************** 升级助手接口 ****************************************************/

export function checkAuth() {
    return request.get(`tk_upgrade/checkauth`)
}
export function getAddonList() {
    return request.get(`tk_upgrade/getaddonlist`)
}
export function installAddon(key: string) {
    return request.post(`tk_upgrade/installaddon/${key}`)
}
export function getVersionList(key: string) {
    return request.get(`tk_upgrade/getversionlist/${key}`)
}
export function installAll() {
    return request.post(`tk_upgrade/installall`)
}
export function updateAll() {
    return request.post(`tk_upgrade/updateall`)
}
export function updateAdmin() {
    return request.post(`tk_upgrade/updateadmin`)
}