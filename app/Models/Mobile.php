<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mobile extends Model
{
    protected $guarded = [];

    // 获取可用手机数-正常的,mobile_group_id=0
    public static function getUsableNum()
    {
        return self::where(['is_normal' => 1, 'mobile_group_id' => 0])->count();
    }

    public static function getExceptionNum()
    {
        return self::where(['mobile_group_id' => 0])->whereIn('is_normal', [0, 2])->count();
    }

    // 分配可用手机
    public static function updateMobileGroupId($mobile_num, $mobile_group_id)
    {
        return self::where('mobile_group_id', '<', 1000)
            ->where('is_normal', 1)
            ->limit($mobile_num)
            ->update(['mobile_group_id' => $mobile_group_id]);
    }
}
