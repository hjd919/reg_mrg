<?php
// $email    = 'TcmIMRglT@yandex.ru';
// $password = 'ZcHMaMe';
// $pwd      = 'did=did&uid=127d497ab3d3c810eb6a1fcf81181de7&pid=-1&cid=-1&t=1524106607&sign=f319b52a5eb3cd20db04f32003259691';
list($script, $email, $password, $pwd) = $argv;
// $email       = 'hjd123hjd@inbox.lv';
// $password    = 'hjd825601';

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
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        // curl_setopt($ch, CURLOPT_PROXY, "118.31.212.185:14202");
        //curl_setopt($ch, CURLOPT_PROXY, "118.31.212.185:" . rand(14202, 14204));
        // curl_setopt($ch, CURLOPT_PROXYUSERPWD, "cn_xs:{$this->pwd}");

        // $fp = tmpfile();
        // curl_setopt($ch, CURLOPT_FILE, $fp);
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
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
$r->host     = 'mail.inbox.lv';
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
