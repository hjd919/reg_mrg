<?php

$email = 'hjd123hjd@inbox.lv';
$password = 'hjd825601';
$command_url = 'pop3s://mail.inbox.lv/1';
// $email       = 'hjd123hjd@inbox.lv';
// $password    = '56aPbfnk';
// $command_url = 'pop3s://pop.mail.ru/';
$port = '993';

// list($script, $email, $password, $command_url, $port, $pwd) = $argv;

// create curl resource
$curl = curl_init();

if ($curl) {
    /* Set username and password */
    curl_setopt($curl, CURLOPT_USERNAME, $email);
    curl_setopt($curl, CURLOPT_PASSWORD, $password);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);

    // curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    // curl_setopt($curl, CURLOPT_PROXY, "118.31.212.185:14202");
    // curl_setopt($curl, CURLOPT_PROXYUSERPWD, "cn_xs:{$pwd}");

    curl_setopt($curl, CURLOPT_URL, $command_url);
    curl_setopt($curl, CURLOPT_PORT, $port);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    // curl_setopt($curl, CURLOPT_USE_SSL, CURLUSESSL_ALL);

    //curl_setopt($curl, CURLOPT_CAINFO, "./certificate.pem");

    curl_setopt($curl, CURLOPT_VERBOSE, true);

    //return the transfer as a string
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    // $output contains the output string
    $output = curl_exec($curl);
}
curl_close($curl);

if (!$output) {
    // file_put_contents('./empty_pop_list.txt', $email . "--" . $password . "\n", FILE_APPEND);
    die('');
} else {
    die($output);
}
