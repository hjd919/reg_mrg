<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    public $table      = 'feedbacks';
    public $timestamps = false;
    protected $guarded = [];
}
