<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpareApp extends Model
{
    protected $guarded = [];

    // 一对多（反向 user
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
