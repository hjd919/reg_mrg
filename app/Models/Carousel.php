<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    public $timestamps  = false;
    protected $fillable = ['*'];

    public function getList()
    {
        return $this->get();
    }
}
