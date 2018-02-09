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
list($script, $email, $password, $command_url, $port, $pwd) = $argv;
// create curl resource
$curl = curl_init();

if ($curl) {
    /* Set username and password */
    curl_setopt($curl, CURLOPT_USERNAME, $email);
    curl_setopt($curl, CURLOPT_PASSWORD, $password);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);

    curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($curl, CURLOPT_PROXY, "118.31.212.185:14202");

    $username = "cn_xs";

    curl_setopt($curl, CURLOPT_PROXYUSERPWD, "{$username}:{$pwd}");

//file_put_contents('./proxy.txt',$username."--".$pwd."\n",FILE_APPEND);

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
    $output = curl_exec($curl);
}
curl_close($curl);
if (!$output) {
    //file_put_contents('./empty_pop_list.txt',$email."--".$password."\n",FILE_APPEND);
    die('');
}

// 查找出在区间(21164-24000)内的邮件id
$line = explode("\r\n", $output);
// Util::log('列表切割后内容', $line);
$content_ids = [];
foreach ($line as $l) {
    if (!trim($l)) {
        continue;
    }
    // id => contentlength
    list($content_id, $content_length) = explode(" ", $l);

    if ($content_length >= 20000 && $content_length <= 24000) {
        $content_ids[] = $content_id;
    }
}
if (!$content_ids) {
    $content_ids = range(1, 10);
}
// Util::log('列表切割后找到苹果邮件content_id', $content_ids);
if ($email == 'mr.KonstantinSchukin1991@mail.ru') {
    file_put_contents('./proxy2.txt', json_encode($content_ids) . "\n", FILE_APPEND);
}

echo json_encode($content_ids);
