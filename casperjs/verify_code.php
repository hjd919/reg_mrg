<?php
// 依赖库
require_once 'Requests.php';

$filepath = empty($argv[1]) ? './capture/capcha.png' : $argv[1];

$try_times = 0;
$capcha    = '';
do {
    // $source    = ; // 验证码截图
    if (class_exists('\CURLFile')) {
        $file = new \CURLFile(realpath($filepath));
    } else {
        $file = '@' . realpath($filepath);
    }

    $dama_url  = "http://api.yundama.com/api.php";
    $username  = '875486058';
    $password  = 'xz123456789';
    $codetype  = '1004';
    $appid     = '4205';
    $timeout   = 20;
    $appkey    = '7eeaeddab5e3c288d88733f603eee88d';
    $method    = 'upload';
    $post_data = compact(
        'username',
        'password',
        'codetype',
        'appid',
        'appkey',
        'timeout',
        'method',
        'file',
        'appid'
    );
    $response2 = Requests::post($dama_url, $post_data); // 上传文件TODO
    $response = json_decode($response2, true);
    $try_times++;
    if ($response['ret']) {
        continue;
    }
    $capcha = $response['text'];
    if ($capcha) {
        break;
    }
    sleep(5);
} while ($try_times < 3);

// 没有验证码返回error
if (!$capcha) {
    echo 'error:' . $response2;
}

echo $capcha;
