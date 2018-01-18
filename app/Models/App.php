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

    // 获取mobile中已用的手机
    public static function getUsedNum()
    {
        return self::where('is_brushing', 1)->count('mobile_num');
    }

    public static function curl($url, $params = array())
    {
        $path    = './cookie.txt';
        $ch      = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        curl_setopt($ch, CURLOPT_COOKIEFILE, $path);
        curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=dn95qf18232ghjco803hlbk1a0");

        if ($params) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

            $headers                 = array();
            $headers['Content-type'] = 'multipart/form-data';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.82 Safari/537.36 QQBrowser/4.0.4035.400');
        curl_setopt($ch, CURLOPT_REFERER, "http://www.chaojiying.cn");

        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);

        $result = curl_exec($ch);
        // print_r($ch);
        curl_close($ch);
        // var_dump(curl_errno($ch));
        // var_dump(curl_getinfo($ch));
        // echo $result;
        // exit;
        return $result;
    }
}
