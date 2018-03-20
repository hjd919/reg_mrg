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
    curl_setopt($curl, CURLOPT_PROXYUSERPWD, "cn_xs:{$pwd}");

    //curl_setopt($curl, CURLOPT_URL, "pop3s://pop.qq.com/1");
    //curl_setopt($curl, CURLOPT_URL, "pop3://pop.mail.ua/");
    //curl_setopt($curl, CURLOPT_PORT, 110);
    //curl_setopt($curl, CURLOPT_URL, "pop3s://pop.mail.ru/5");
    curl_setopt($curl, CURLOPT_URL, $command_url);
    curl_setopt($curl, CURLOPT_PORT, $port);

    curl_setopt($curl, CURLOPT_USE_SSL, CURLUSESSL_ALL);

    //curl_setopt($curl, CURLOPT_CAINFO, "./certificate.pem");

    // curl_setopt($curl, CURLOPT_VERBOSE, true);

    //return the transfer as a string
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    // $output contains the output string
    $content = curl_exec($curl);
    curl_close($curl);
//     print_r($content);
    if (!$content) {
        file_put_contents('empty_content.txt', "\n---url:{$command_url};email:$email;password:$password---\n".$content . "\n", FILE_APPEND);
        die('');
    } else {
        //file_put_contents('./pop_content.txt', date('Y-m-d H:i:s') . '--' . $content . "\n", FILE_APPEND);
    }
//     echo "--------\n";
    //     $curl = curl_init();
    //     curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    //     curl_setopt($curl, CURLOPT_PROXY, "118.31.212.185:14202");
    //     curl_setopt($curl, CURLOPT_PROXYUSERPWD, "cn_xs:{$pwd}");
    //     curl_setopt($curl, CURLOPT_URL, 'http://2017.ip138.com/ic.asp');
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     $output = curl_exec($curl);
    //     curl_close($curl);
    // // print_r($res);
    //     die($output);

}

    //file_put_contents('url.txt', "\n---url:{$command_url}". "\n", FILE_APPEND);
// 从苹果邮件匹配获取code
if (preg_match('#x-ds-vetting-token: (.*?)\r\n#', $content, $match)) {
    //file_put_contents('has_token.txt', "\n---url:{$command_url}".$content . "\n", FILE_APPEND);
    echo $match[1];
} else {
    //file_put_contents('no_token.txt', "\n---url:{$command_url}".$content . "\n", FILE_APPEND);
    echo '';
}
