<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HourlAppStat extends Model
{
    protected $table   = 'hourl_app_stat';
    protected $guarded = [];

    // 一对多（反向 ios_app
    public function app()
    {
        return $this->belongsTo('App\Models\App');
    }
}
