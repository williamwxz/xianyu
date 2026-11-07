<?php
// +----------------------------------------------------------------------
// | Author: TK
// +----------------------------------------------------------------------
namespace addon\tk_upgrade\app\job;

use app\model\sys\SysScheduleLog;
use core\base\BaseJob;
use think\facade\Log;

class ClearScheduleLog extends BaseJob
{

    public function doJob()
    {
        try {
            $scheduleLogModel = new SysScheduleLog();
            // 添加删除条件，分页删除避免大数据量问题
            $deletedRows = $scheduleLogModel
                ->where(['status'=>'success'])
                ->limit(100)
                ->delete();
            return $deletedRows > 0;
        } catch (\Exception $e) {
            // 添加详细错误日志
            Log::write("清理失败：{$e->getMessage()} [{$e->getFile()}:{$e->getLine()}]");
            Log::write("堆栈跟踪：{$e->getTraceAsString()}");
            return false;
        }
    }

}
