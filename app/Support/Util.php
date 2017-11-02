<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 *
 */
class Util
{
    public static function errorLog($title, $data)
    {
        Log::error($title . ':' . var_export($data, true));
    }

    public static function log($title, $data)
    {
        Log::info($title . ':' . var_export($data, true));
    }

    public static function die_jishua($message, $code = 0)
    {
        if (is_array($message) || is_object($message)) {
            // 成功
            $data = array(
                'data' => $message,
                'msg'  => 'ok',
                'ret'  => 0,
            );
        } else {
            if (0 == $code) {
                // 成功
                $data = array(
                    'data' => [
                        'msg' => $message,
                    ],
                    'msg'  => $message,
                    'ret'  => 0,
                );
            } else {
                // 失败
                $data = array(
                    'data' => [
                        'msg' => $message,
                    ],
                    'msg'  => $message,
                    'ret'  => 1,
                );
            }

        }
        header("Content-type: application/json; charset=utf-8");
        $json_str = json_encode($data, JSON_UNESCAPED_UNICODE);
        die($json_str);
    }

    public static function die_json($message, $code = 0)
    {
        if (is_array($message) || is_object($message)) {
            $data = array(
                'error_code' => 0,
                'data'       => $message,
            );
        } else {
            $data = array(
                'error_code' => $code,
                'message'    => $message,
            );
        }

        header("Content-type: application/json; charset=utf-8");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header("Access-Control-Max-Age: 20");
        $json_str = json_encode($data, JSON_UNESCAPED_UNICODE);
        // if (isset($_REQUEST['callback'])) {
        //     $cb = $_REQUEST['callback'];
        //     die($cb . "(" . $json_str . ")");
        // }

        die($json_str);
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

    // 所有的缓存key
    public static function cacheKey($type, $params)
    {
        extract($params);
        switch ($type) {
            case 'doing_app_id':
                return "doing:app_id:u_{$user_id}";
                break;

            default:
                # code...
                break;
        }
    }
}
