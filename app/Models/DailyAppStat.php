<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyAppStat extends Model
{
    protected $table   = 'daily_app_stat';
    protected $guarded = [];

    // 一对多（反向 ios_app
    public function app()
    {
        return $this->belongsTo('App\Models\App', 'appid', 'appid');
    }
}
