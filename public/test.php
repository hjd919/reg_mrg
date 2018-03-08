<?php
$curl = curl_init();

$command_url = 'http://2017.ip138.com/ic.asp';
if ($curl) {
    /* Set username and password */
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);

    curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($curl, CURLOPT_PROXY, "47.74.174.69:14202");

    $username = "cn_xs";

    curl_setopt($curl, CURLOPT_PROXYUSERPWD, "cn_xs:did=did&uid=f6ea1cf05097a1a198e9200f5b2ba105&sid=-1&pid=-1&cid=-1&t=1519716093&sign=544ff6e43100ff3fca8b1bc16d5afeef");

//file_put_contents('./proxy.txt',$username."--".$pwd."\n",FILE_APPEND);

    //curl_setopt($curl, CURLOPT_URL, "pop3s://pop.qq.com/1");
    //curl_setopt($curl, CURLOPT_URL, "pop3://pop.mail.ua/");
    //curl_setopt($curl, CURLOPT_PORT, 110);
    //curl_setopt($curl, CURLOPT_URL, "pop3s://pop.mail.ru/5");
    curl_setopt($curl, CURLOPT_URL, $command_url);
    curl_setopt($curl, CURLOPT_PORT, $port = 80);

    curl_setopt($curl, CURLOPT_USE_SSL, CURLUSESSL_ALL);

    //curl_setopt($curl, CURLOPT_CAINFO, "./certificate.pem");

    //curl_setopt($curl, CURLOPT_VERBOSE, true);

    //return the transfer as a string
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    // $output contains the output string
    $output = curl_exec($curl);
    echo $output;
}
curl_close($curl);
