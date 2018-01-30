<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $guarded = [];

    // 一对多（反向 ios_app
    public function ios_app()
    {
        return $this->belongsTo('App\Models\IosApp');
    }

    // 一对多（反向 user
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public static function add($user_id, $ios_app_id, $appid, $app_name, $step = 1, $task_type = 1)
    {
        $task_id = self::insertGetId(compact(
            'user_id',
            'ios_app_id',
            'appid',
            'app_name',
            'task_type',
            'step'
        ));
        return $task_id;

    }
}
