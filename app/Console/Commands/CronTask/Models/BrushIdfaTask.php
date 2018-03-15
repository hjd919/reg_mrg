<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrushIdfaTask extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    // 统计这个app的有效量
    public static function countSuccessBrushNum($appid, $app_id, $start_time)
    {
        return self::where([
            ['brush_idfa_id', '=', $app_id],
            ['created_at', '>=', $start_time],
            ['status', '>=', 1],
        ])->count();
    }
}
