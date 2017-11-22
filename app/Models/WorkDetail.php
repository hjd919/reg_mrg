<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorkDetail extends Model
{
    protected $table   = 'work_detail';
    protected $guarded = [];

    // 获取可刷数
    public static function getUsableBrushNum($appid)
    {
        $used_num  = self::where('appid', $appid)->count();
        $total_num = DB::table('emails')->where('is_valid', 301)->where('valid_status', 1)->count();
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
        $table     = $table_key ? $table_key : '';
        $obj       = new self;
        $obj->setTable($table);
        return $obj;
    }
}
