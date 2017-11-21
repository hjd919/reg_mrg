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
}
