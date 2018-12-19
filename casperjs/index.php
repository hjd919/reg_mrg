<?php

date_default_timezone_set('Asia/Shanghai');

// $ppid = posix_getpid();
// $pid = pcntl_fork();
// if ($pid == -1) {
//     throw new Exception('fork子进程失败!');
// } elseif ($pid > 0) {
//     cli_set_process_title("我是父进程,我的进程id是{$ppid}.");
//     sleep(30);
// } else {
//     $cpid = posix_getpid();
//     cli_set_process_title("我是{$ppid}的子进程,我的进程id是{$cpid}.");
//     sleep(30);
// }
$date = date('ymd');
$file = "emails.txt";
$t1   = microtime(true);
for ($i = 0; $i < 1; $i++) {
    // 获取代理ip
    $content    = file_get_contents('http://jsapi.yz210.com/appleid/task/getproxy');
    $proxy      = json_decode($content, true);
    $proxy_auth = "{$proxy['user']}:{$proxy['password']}";
    for ($j = 0; $j < 1; $j++) {
        $key        = md5(microtime(true) . rand(1, 100));
        $email_name = substr($key, 0, 6);
        $password   = '';
        file_put_contents($file, "{$email_name}\n", FILE_APPEND);
        reg_ru($email_name, $proxy_auth);
    }
}
$t2 = microtime(true);

echo "花费时间" . ($t2 - $t1);

function reg_ru($email_name, $proxy_auth)
{
    echo "p---$proxy_auth";
    // 调用注册
    $cmd = "casperjs --ignore-ssl-errors=true --proxy-type=socks5 --proxy='118.31.212.185:14203' --proxy-auth='{$proxy_auth}' --web-security=no reg_inbox.js >> log.txt";
    exec($cmd, $result);
    print_r($result);
    return true;
}
