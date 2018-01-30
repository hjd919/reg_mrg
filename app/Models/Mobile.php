<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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
        return self::where('mobile_group_id', 0)
            ->where('is_normal', 1)
            ->limit($mobile_num)
            ->update(['mobile_group_id' => $mobile_group_id]);
    }

    // 获取mobile中已用的手机
    public static function getUsedNum()
    {
        return self::where('mobile_group_id', '<', 1000)->where('mobile_group_id', '>', 1)->count();
    }

    // 分配mobile_id给手机数量
    public static function setMobileGroupId($mobile_num, $mobile_group_id = null)
    {
        if (!$mobile_group_id) {
            $mobile_group_id = DB::table('config')->where('keyword', 'next_mobile_group_id')->value('value');
            // 设置下一个mobile_group_id
            if ($mobile_group_id >= 999) {
                $value = 1;
            } else {
                $value = $mobile_group_id + 1;
            }
            DB::table('config')->where('keyword', 'next_mobile_group_id')->update(['value' => $value]);
            // 如果不存在分组，则添加分组表
            if (!DB::table('mobile_group')->find($mobile_group_id)) {
                DB::table('mobile_group')->insert([
                    'id'     => $mobile_group_id,
                    'name'   => '随机' . $mobile_group_id,
                    'remark' => 'no remark',
                ]);
            }
        }

        // 更新手机分组（1000以上是自己用的)
        self::updateMobileGroupId($mobile_num, $mobile_group_id);

        return $mobile_group_id;
    }

    public static function count_mobile_num($total_hour, $success_num)
    {
        $total_hour *= 35;
        $mobile_num = round($success_num / $total_hour);
        return $mobile_num <= 0 ? 1 : $mobile_num;
    }
}
