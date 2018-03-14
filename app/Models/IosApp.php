<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IosApp extends Model
{
    protected $guarded = [];

    // 一对多 tasks
    public function tasks()
    {
        return $this->hasMany('App\Models\Task');
    }
}
