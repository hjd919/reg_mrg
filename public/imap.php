<?php
list($script, $email, $password) = $argv;
class R
{
    public $host;
    public $username;
    public $password;
    public $port;
    public $type;
    public $timeout = 15;
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
        // $fp = tmpfile();
        // curl_setopt($ch, CURLOPT_FILE, $fp);
        // curl_setopt($ch, CURLOPT_VERBOSE, false);
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
$r->port     = '993';
// echo $result = $r->write('STATUS "INBOX" (MESSAGES)');
// die;
$result = $r->write('', 3);
if (!preg_match('#base64[\r\n]+(.*?)[\r\n]+--==#s', $result, $match)) {
    file_put_contents('log.txt', date('Y-m-d') . "---result:{$result}" . "\n", FILE_APPEND);
    return false;
}
$content = base64_decode($match[1]);

if (preg_match('#x-ds-vetting-token: (.*?)\r\n#', $content, $match)) {
    //file_put_contents('has_token.txt', "\n---url:{$command_url}".$content . "\n", FILE_APPEND);
    echo $match[1];
} else {
    //file_put_contents('no_token.txt', "\n---url:{$command_url}".$content . "\n", FILE_APPEND);
    echo '';
}
