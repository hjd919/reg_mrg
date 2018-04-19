<?php
// $email    = 'vjwDugx.4vOPPKoLo.8@yandex.ru';
// $password = 'DaWPcK0bIC';
// $pwd      = 'did=did&uid=127d497ab3d3c810eb6a1fcf81181de7&pid=-1&cid=-1&t=1524106607&sign=f319b52a5eb3cd20db04f32003259691';
list($script, $email, $password, $pwd) = $argv;
class R
{
    public $host;
    public $username;
    public $password;
    public $port;
    public $type;
    public $pwd;
    public $timeout = 20;
    public $secure  = true;
    public function write($command = '', $uid = 0)
    {
        $uid_str = $uid ? '/INBOX/;UID=' . $uid : '';
        $url     = ($this->type == 'imap' ? 'imap' : 'pop3') . ($this->secure
            ? 's' : '') . '://' . $this->host . $uid_str;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PORT, $this->port);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        // curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_PROXY, "118.31.212.185:14202");
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, "cn_xs:{$this->pwd}");

        // $fp = tmpfile();
        // curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        // $verbose = fopen('php://temp', 'w+');
        // curl_setopt($ch, CURLOPT_STDERR, $verbose);
        if ($command) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $command);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        // rewind($verbose);
        // $verboseLog = stream_get_contents($verbose);
        // // echo ($verboseLog);
        // fclose($verbose);
        return $res;
    }
}
$r           = new R();
$r->type     = 'imap';
$r->host     = 'imap.yandex.ru';
$r->username = $email;
$r->password = $password;
$r->pwd      = $pwd;
$r->port     = '993';
$result      = $r->write('STATUS "INBOX" (MESSAGES)');
if (!preg_match("/MESSAGES (\d+)\)/", $result, $match)) {
    die('is_feng');
}
$email_num = $match[1];
$content   = $r->write('', $email_num);
$code      = '';
if (preg_match('#x-ds-vetting-token: (.*?)\r\n#', $content, $match)) {
    $code = $match[1];
}
echo $code;
die;

// die;
// die;
$code = 0;
for ($i = 2; $i <= 5; $i++) {
    $content = $r->write('', $i);
    if ($i == 2 && !$content) {
        if (!$content) {
            echo 'is_feng';
            die;
        } else {
            continue;
        }
    }
    if (!$content) {
        break;
    }
    if (preg_match('#x-ds-vetting-token: (.*?)\r\n#', $content, $match)) {
        $code = $match[1];
    }
}
echo $code;
die;
// echo $result."\n";
// if (!preg_match('#base64[\r\n]+(.*?)[\r\n]+--==#s', $result, $match)) {
//     file_put_contents('log.txt', date('Y-m-d') . "---result:{$result}" . "\n", FILE_APPEND);
//     return false;
// }
// $content = base64_decode($match[1]);
// echo $content."\n";
$content = $r->write('', 3);
if (preg_match('#x-ds-vetting-token: (.*?)\r\n#', $content, $match)) {
    echo $match[1];
} else {
    echo '';
}
