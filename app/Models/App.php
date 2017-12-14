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
}
