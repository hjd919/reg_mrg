<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WorkDetail extends Model
{
    protected $table   = 'work_detail';
    public $timestamps = false;
    protected $guarded = [];

    // 获取可刷数
    public static function getUsableBrushNum($appid)
    {
        // $used_num = self::getWorkDetailTable($appid)->where('appid', $appid)->count();
        $ios_app = DB::table('ios_apps')->where('appid', $appid)->first();

        $min_num = DB::table('emails')->where('id', '>', $ios_app->max_account_id)->where('valid_status', 1)->count();
        $max_num = DB::table('emails')->where('id', '<', $ios_app->min_account_id)->where('valid_status', 1)->count();

        $used_num = Redis::get('used_appid:' . $appid);
        $used_num = (int) $used_num;

        return 50000;

        return $min_num + $max_num - $used_num;
    }

    // 统计总刷数
    public static function countBrushedNum($appid, $app_id)
    {
        $brushed_num = self::getWorkDetailTable($appid)->where('app_id', $app_id)->count();
        return $brushed_num;
    }

    // 统计成功刷数
    public static function getSuccessBrushedNum($appid, $app_id)
    {
        $success_brushed_num = self::getWorkDetailTable($appid)->where(['app_id' => $app_id, 'status' => 3])->count();
        return $success_brushed_num;
    }

    // 获取workdetail表
    public static function getWorkDetailTable($appid)
    {
        // 根据appid获取表后缀
        $table_key = Redis::hGet('work_detail_table', $appid);
        $table     = 'work_detail' . ($table_key ? $table_key : '');
        $obj       = new self;
        $obj->setTable($table);
        return $obj;
    }

    // 判断是否app刷过此批量账号
    public static function isAppBrushEmails($appid, $account_ids)
    {
        return self::getWorkDetailTable($appid)
            ->where('appid', $appid)
            ->whereIn('account_id', $account_ids)
            ->select('id')
            ->get()
            ->toArray();
    }

    // 判断是否app刷过此批量设备信息
    public static function isAppBrushDevices($appid, $device_ids)
    {
        return self::getWorkDetailTable($appid)
            ->where('appid', $appid)
            ->whereIn('device_id', $device_ids)
            ->select('id')
            ->get()
            ->toArray();
    }

    // 添加
    public static function add($appid, $work_detail)
    {
        return self::getWorkDetailTable($appid)->insert($work_detail);
    }

    // 更新状态
    public static function updateStatus($work_id, $account_id, $status)
    {
        // 根据work_id查询appid
        $appid = DB::table('works')->select('appid')->where('id', $work_id)->value('appid');

        return self::getWorkDetailTable($appid)->where([
            'work_id'    => $work_id,
            'account_id' => $account_id,
        ])->update([
            'status'      => $status,
            'report_time' => date('Y-m-d H:i:s'),
        ]);
    }

    // 统计这个app的有效量
    public static function countSuccessBrushNum($appid, $app_id, $start_time)
    {
        return self::getWorkDetailTable($appid)->where([
            ['app_id', '=', $app_id],
            ['create_time', '>=', $start_time],
            ['status', '=', 3],
        ])->count();
    }

    // 统计这个任务上一小时的量级情况
    public static function countBrushedNumLastHour($appid, $app_id, $start_hour = null, $where = [])
    {
        $brushed_num = self::getWorkDetailTable($appid)
            ->where('app_id', $app_id)
            ->where('create_time', '>=', $start_hour)
            ->where('create_time', '<=', date('Y-m-d H', strtotime('+1 hours', strtotime($start_hour))))
            ->where($where)
            ->count();
        return $brushed_num;
    }

    // 获取该app最小account_id
    public static function getMinAccountId($appid)
    {
        $min_id = self::getWorkDetailTable($appid)->where('appid', $appid)->min('account_id');
        return $min_id;
    }

    // 获取该app最大account_id
    public static function getMaxAccountId($appid)
    {
        $min_id = self::getWorkDetailTable($appid)->where('appid', $appid)->max('account_id');
        return $min_id;
    }
}
