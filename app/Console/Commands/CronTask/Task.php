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
}
