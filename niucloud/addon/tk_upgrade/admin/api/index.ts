
import request from '@/utils/request'

/***************************************************** 便捷工具 ****************************************************/
export function repairBottom() {
    return request.get(`tk_upgrade/repairbottom`)
}
export function repairBottomAdmin() {
    return request.get(`tk_upgrade/repairbottom/admin`)
}