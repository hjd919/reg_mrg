<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 *
 */
class Util
{
    static $mobile_id = 0;

    public static function errorLog($title, $data = null)
    {
        if (!$data) {
            $data = $title;
        }

        Log::error($title . ':' . var_export($data, true));
    }

    public static function log($title, $data = null)
    {
        if (!$data) {
            $data = $title;
        }

        Log::info($title . ':' . var_export($data, true));
    }

    public static function getIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }
}
