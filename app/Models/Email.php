<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $guarded = [];

    public static function get_last_id_key($appid)
    {
        return 'last_email_id:appid_' . $appid;
    }
}
