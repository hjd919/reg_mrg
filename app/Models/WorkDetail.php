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
        $used_num  = self::getWorkDetailTable($appid)->where('appid', $appid)->count();
        $total_num = DB::table('emails')->where('valid_status', 1)->count();
        return $total_num - $used_num;
    }

    // 统计总刷数
    public static function countBrushedNum($app_id)
    {
        $brushed_num = self::where('app_id', $app_id)->count();
        return $brushed_num;
    }

    // 统计成功刷数
    public static function getSuccessBrushedNum($app_id)
    {
        $success_brushed_num = self::where(['app_id' => $app_id, 'status' => 3])->count();
        return $success_brushed_num;
    }

    public static function test()
    {
        $success_brushed_num = self::getWorkDetailTable($appid)->limit(10)->get();
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
    public static function isAppBrushEmails($appid, $emails)
    {
        return self::getWorkDetailTable($appid)
            ->where('appid', $appid)
            ->whereIn('email', $emails)
            ->pluck('email')
            ->toArray();
    }

    // 判断是否app刷过此批量设备信息
    public static function isAppBrushDevices($appid, $udids)
    {
        return self::getWorkDetailTable($appid)
            ->where('appid', $appid)
            ->whereIn('udid', $udids)
            ->pluck('udid')
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
}
