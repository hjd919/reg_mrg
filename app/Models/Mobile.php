<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mobile extends Model
{
    protected $guarded = [];

    // 获取可用手机数-正常的,小于1000
    public static function getUsableNum()
    {
        return self::where([['is_normal', '=', 1], ['mobile_group_id', '<', 1000]])->count();
    }

    public static function updateMobileGroupId($mobile_num, $mobile_group_id)
    {
        return self::where('mobile_group_id', '<', 1000)
            ->where('is_normal', 1)
            ->limit($mobile_num)
            ->update(['mobile_group_id' => $mobile_group_id]);
    }
}
