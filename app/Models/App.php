<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    public $timestamps = false;

    protected $guarded = [];
    // 一对多（反向 user
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    // 获取mobile中已用的手机
    public static function getUsedNum()
    {
        return self::where('is_brushing', 1)->count('mobile_num');
    }
}
