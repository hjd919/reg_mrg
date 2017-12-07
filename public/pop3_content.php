<?php
/*
$email = $_GET['email'];
$password = $_GET['password'];
$command_url = $_GET['comand_url'];
$port = $_GET['port'];

$email = 'AurikaFomina1997@mail.ua';
$password = '7bwlHGHxh';
$command_url = 'pop3s://pop.mail.ru/5';
$port = '995';
 */
list($script, $email, $password, $command_url, $port) = $argv;
// create curl resource
$curl = curl_init();

if ($curl) {
    /* Set username and password */
    curl_setopt($curl, CURLOPT_USERNAME, $email);
    curl_setopt($curl, CURLOPT_PASSWORD, $password);
    curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($curl, CURLOPT_PROXY, "118.31.212.185:14202");
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);

    $username = "cn_xs";
    $did       = 'did';
    $uid       = md5(microtime(true));
    $pid       = 0;
    $cid       = 0;
    $timestamp = time();
    $key       = "Al0MF4fizqjbM9Ql";

    $str1 = "did={$did}&uid={$uid}&pid={$pid}&cid={$cid}&t={$timestamp}&key={$key}";
    $sign = md5($str1);
    $pwd  = "did={$did}&uid={$uid}&pid={$pid}&cid={$cid}&t={$timestamp}&sign={$sign}";
    curl_setopt($curl, CURLOPT_PROXYUSERPWD, "{$username}:{$pwd}");

    //curl_setopt($curl, CURLOPT_URL, "pop3s://pop.qq.com/1");
    //curl_setopt($curl, CURLOPT_URL, "pop3://pop.mail.ua/");
    //curl_setopt($curl, CURLOPT_PORT, 110);
    //curl_setopt($curl, CURLOPT_URL, "pop3s://pop.mail.ru/5");
    curl_setopt($curl, CURLOPT_URL, $command_url);
    curl_setopt($curl, CURLOPT_PORT, $port);

    curl_setopt($curl, CURLOPT_USE_SSL, CURLUSESSL_ALL);

    //curl_setopt($curl, CURLOPT_CAINFO, "./certificate.pem");

    //curl_setopt($curl, CURLOPT_VERBOSE, true);

    //return the transfer as a string
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    // $output contains the output string
    $content = curl_exec($curl);
    if(!$content) die('');
}


curl_close($curl);

// 从苹果邮件匹配获取code
if (preg_match('#x-ds-vetting-token: (.*?)\r\n#', $content, $match)) {
    echo $match[1];
} else {
//    file_put_contents('content.txt', json_encode(compact('content'))."\n", FILE_APPEND);
    echo '';
}
