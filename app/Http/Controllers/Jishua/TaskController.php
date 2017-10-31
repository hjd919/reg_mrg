<?php
namespace App\Http\Controllers\Jishua;

use App\Http\Controllers\Controller;
use App\Support\Util;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // 获取任务
    public function get(
        Request $request
    ) {
        $data = array(
            0 => array(
                'work_id'    => '463',
                'app_id'     => '1273960275',
                'keyword'    => '色彩游戏',
                'app_name'   => 'com.zb.ColorGame',
                'account_id' => '10126',
                'email'      => 'x4zff9q@icloud.com',
                'password'   => 'Xx223322',
                'serial'     => 'F6WP4FHDG5MQ',
                'imei'       => '358359067750420',
                'bt'         => 'a4:5e:60:8e:c3:ae',
                'wifi'       => 'a4:5e:60:8e:c3:ad',
                'udid'       => '3e6443f2fd64fcd170f9353d232bfedaf4d1af96',
            ),
            1 => array(
                'work_id'    => '463',
                'app_id'     => '1273960275',
                'keyword'    => '色彩游戏',
                'app_name'   => 'com.zb.ColorGame',
                'account_id' => '10119',
                'email'      => 'x57h9p0@icloud.com',
                'password'   => 'Xx223322',
                'serial'     => 'DNQPJSS4G5MP',
                'imei'       => '359283060202404',
                'bt'         => '9c:fc:01:e5:0e:59',
                'wifi'       => '9c:fc:01:e5:0e:58',
                'udid'       => '15135048187c96386453f2630a10de002941a2b4',
            ),
            2 => array(
                'work_id'    => '468',
                'app_id'     => '1273960275',
                'keyword'    => '色彩大师',
                'app_name'   => 'com.zb.ColorGame',
                'account_id' => '10608',
                'email'      => 'w3r8dms@icloud.com',
                'password'   => 'Xx223322',
                'serial'     => 'FFMQT02KG5MQ',
                'imei'       => '355393071552566',
                'bt'         => '60:a3:7d:77:56:fc',
                'wifi'       => '60:a3:7d:77:56:fb',
                'udid'       => 'a2c9de246d3de162dbd09f67f199cb3178907475',
            ),
        );
        Util::die_jishua($data);
    }

    // 上报任务
    public function report(
        Request $request
    ) {
        Util::die_jishua('ok');
    }
    // 上报失败账号
    public function invalid_account(
        Request $request
    ) {
        Util::die_jishua('ok');
    }
}
