<?php
require './vendor/autoload.php';
// register_shutdown_function("fatalError");

// function fatalError()
// {
//     $error = error_get_last();
//     file_put_contents(date('Y-m-d') . '.log', $error['message']);
// }

// ini_set('log_errors','on');    //开启日志写入功能
// ini_set('error_log',date('Y-m-d').'_error.log'); //日志的存放位置
// ini_set('display_errors','off');    //屏蔽页面显示
// error_reporting(E_ERROR);    //输出所有错误

$pid   = 10000;
$count = 10000;
if (!empty($argv[1])) {
    $pid = $argv[1];
}

if (!empty($argv[2])) {
    $count = $argv[2];
}

$i     = 0;
$login = new Login($pid);
while ($i < $count) {

    $login->run();
    $i++;
    if ($i % 3 == 0) {
        $login = new Login($pid);
    }
}

class Login
{
    private $header;
    private $cookies;
    private $captcha;
    private $questions;
    private $track_id;
    private $resCaptcha;
    private $time;
    private $csrf_token;
    private $account;
    private $pwd;
    private $answer;
    private $info;
    private $error;
    private $num;

    private $proxy;
    private $proxyuser;
    private $proxypwd;
    private $proxyip;

    private $pid;
    public function __construct($i)
    {
        $this->pid = $i;
    }
    public function info($info)
    {
        $msg = '>>>>' . $info . '  ' . date('Y-m-d H:i:s') . PHP_EOL;
        echo $msg;
        // file_put_contents('./logs/' . date('Y-m-d') . $this->pid . '.log', $msg, FILE_APPEND);
    }

    public function checkProxy()
    {
        $this->info('检查ip...');
        $res = Requests::get('http://2017.ip138.com/ic.asp', [], ['proxy' => [$this->proxy, $this->proxyuser, $this->proxypwd]]);

        $body = iconv('GB2312', 'UTF-8', $res->body);
        preg_match('/\[(.*)\]/', $body, $match);
        if (empty($match[1])) {
            $this->info('ip 检查不出来');
            $this->proxyip = '127.0.0.1';
        } else {
            $this->proxyip = $match[1];
            $this->info('ip:' . $match[1]);
        }

    }
    public function run($account = '', $pwd = '')
    {
        static $iprev;
        if (!$iprev) {
            $iprev++;
        }

        $this->header = $this->getHeader();
        /*
        try {

        $this->checkProxy();

        } catch (Throwable $t) {

        }*/
        try {

            $this->info('[[[[开始...');
            $this->getCookies();
            if (!empty($this->error)) {
                return;
            }
            $this->info('生成账号信息..');

            // $this->getQuestions();
            // if (!empty($this->error)) {
            //     return;
            // }
            $this->genAccountInfo();
            $this->getCaptcha();
            if (!empty($this->error)) {
                return;
            }
            $this->textCaptcha();
            if (!empty($this->error)) {
                return;
            }
            $this->checkHuman();
            if (!empty($this->error)) {
                return;
            }

        } catch (Throwable $t) {
            echo $t->getMessage() . PHP_EOL;
            $this->info($t->getMessage());
            return;
        }

        try {
            $res = $this->registration();
            $this->info('...结束]]]');
            $this->info('账号信息：' . json_encode($res));
            if (!empty($res)) {
                $dir = "./data/" . date('Ymd');

                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                file_put_contents($dir . '/' . $this->pid . ".csv", $res['account'] . '@yandex.ru----' . $res['password'] . PHP_EOL, FILE_APPEND);
            }
        } catch (Throwable $t) {
            file_put_contents($dir . '/' . $this->pid . ".csv", $res['account'] . '@yandex.ru----' . $res['password'] . PHP_EOL, FILE_APPEND);
            //file_put_contents("./data/" . $this->pid . ".csv", $this->account . ',' . $this->pwd . ',' . $this->proxyip . ',!ok' . PHP_EOL, FILE_APPEND);
        }
    }

    public function genAccountInfo()
    {
        $letters = ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', '.', 'G', 'H', 'J', 'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm'];

        $account = [];
        while (true) {

            $account = randName();
            if ($account[0] == '.' || $account[0] == '_' || intval($account[0]) != 0) {
                $account = [];
                continue;
            }

            if ($this->checkAccount($account)) {
                $this->account = $account;
                $this->info('account:' . $this->account);
                $this->pwd = randName(6, 10);
                sleep(1);
                break;
            } else {
                $this->info('account：' . $account . '不能使用');
                continue;
            }
            sleep(1);
            break;
        }

        $this->answer    = randName(5, 50);
        $this->firstname = randName(3, 10);
        $this->lastname  = randName(3, 10);
    }

    public function checkAccount($account)
    {
        $this->info('检查账号：' . $account);
        $parameters = [
            'track_id'   => $this->track_id,
            'csrf_token' => $this->csrf_token,
            'login'      => $account,
        ];
        $response = Requests::post('https://passport.yandex.ru/registration-validations/login', $this->getAjaxHead(), $parameters, ['cookies' => $this->cookies, 'proxy' => [$this->proxy, $this->proxyuser, $this->proxypwd]]);
        $json     = json_decode($response->body, true);
        if (empty($json) || empty($json['status']) || $json['status'] != 'ok') {
            return false;
        }
        return true;
    }

    public function checkHuman()
    {
        $this->info('检查验证码' . $this->resCaptcha);
        $parameters = [
            'track_id'   => $this->track_id,
            'csrf_token' => $this->csrf_token,
            'answer'     => $this->resCaptcha,
        ];
        $this->info('检查验证码res：' . json_encode($parameters));
        $response = Requests::post('https://passport.yandex.ru/registration-validations/checkHuman', $this->getAjaxHead(), $parameters, ['cookies' => $this->cookies, 'proxy' => [$this->proxy, $this->proxyuser, $this->proxypwd]]);
        $json     = json_decode($response->body, true);
        if ($json['status'] != 'ok') {
            $this->info('验证验证码失败');
            return $this->error = '验证验证码失败';
        }
        $this->info('验证码正确');
    }

    public function textCaptcha()
    {
        $url = $this->captcha['image_url'];
        $this->info('下载验证码...');

        $base = '';
        for ($i = 0; $i < 5; $i++) {
            $bin = http($url);
            if (!$bin) {
                $this->info('下载验证码失败尝试第' . $i . '...');
                continue;
            }
            $base = 'data:image/gif;base64,' . chunk_split(base64_encode($bin));
            break;
        }

        if (!$base) {
            $this->error = '下载验证失败';
            return;
        }
        $image = time() . '.gif';
        file_put_contents('./tmp/' . $image, $bin);
        $base = $this->base64EncodeImage('./tmp/' . $image);

        $parameters = [
            'method' => 'base64',
            'key'    => '89aa4c53edbaae6e6e21521dd9318fc7',
            'body'   => $base,
        ];
        $this->info('上传验证码...');
        $captchaKey = '';
        for ($i = 0; $i < 5; $i++) {
            try {
                $response = Requests::post('http://2captcha.com/in.php', [], $parameters, ['timeout' => 6]);
                $key      = explode('|', $response->body);
                if (empty($key[1])) {
                    $this->info('上传验证码失败');
                    $this->info('尝试第' . $i . '上传');
                    continue;
                }
                $captchaKey = $key[1];
            } catch (Throwable $t) {
                $this->info('上传验证码失败' . $t->getMessage());
                $this->info('尝试第' . $i . '上传');
                continue;
            }

            break;
        }

        if (!$captchaKey) {
            $this->error = '上传验证码失败';
            return;
        }
        $this->info('等待验证....');
        sleep(3);
        for ($i = 0; $i < 10; $i++) {
            $response = http('http://2captcha.com/res.php?key=89aa4c53edbaae6e6e21521dd9318fc7&action=get&id=' . $captchaKey);
            $this->info($response);
            $arr              = explode('|', $response);
            $this->resCaptcha = !empty($arr[1]) ? $arr[1] : '';
            if (!$this->resCaptcha) {
                $this->info('解析验证码失败');
                sleep(1);
                continue;
            } else {
                $this->info('验证码为：' . $this->resCaptcha);
            }
            file_put_contents('./tmp/' . $this->resCaptcha . '.gif', $bin);
            break;
        }
        if (!$this->resCaptcha) {
            return $this->error = '解析验证码失败';
        }
        return $this->resCaptcha;
    }

    public function base64EncodeImage($image_file)
    {
        $base64_image = '';
        $image_info   = getimagesize($image_file);
        $image_data   = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }
    public function registration()
    {
        $parameters = [
            "track_id"             => $this->track_id,
            "csrf_token"           => $this->csrf_token,
            "firstname"            => $this->firstname,
            "lastname"             => $this->lastname,
            "login"                => $this->account,
            "password"             => $this->pwd,
            "password_confirm"     => $this->pwd,
            "hint_question_id"     => "12",
            "hint_question"        => "Фамилия вашего любимого музыканта",
            "hint_question_custom" => "",
            "hint_answer"          => randName(10, 25),
            "captcha"              => $this->resCaptcha,
            "phone"                => "",
            "phoneCode"            => "",
            "human-confirmation"   => "captcha",
            "from"                 => "mail",
            "eula_accepted"        => "on",
        ];

        $this->info('开始注册，内容为：' . json_encode($parameters));
        sleep(3);
        $response = Requests::post('https://passport.yandex.ru/registration-validations/registration-alternative', $this->getAjaxHead(), $parameters, ['cookies' => $this->cookies, 'proxy' => [$this->proxy, $this->proxyuser, $this->proxypwd]]);
        $this->info($response->body);
        $json = json_decode($response->body, true);
        if (empty($json['status']) || $json['status'] != 'ok') {
            $this->info('最后一步失败');
            $this->error = '最后一步失败';
            return;
        }

        return ['account' => $this->account, 'password' => $this->pwd, 'answer' => $this->answer];
    }

    public function getCaptcha()
    {
        $this->info('获取验证码');
        $parameters = [
            'track_id'   => $this->track_id,
            'csrf_token' => $this->csrf_token,
            'language'   => 'ru',
        ];

        for ($i = 0; $i < 5; $i++) {
            try {
                $questions     = Requests::post('https://passport.yandex.ru/registration-validations/textcaptcha', $this->getAjaxHead(), $parameters, ['cookies' => $this->cookies, 'proxy' => [$this->proxy, $this->proxyuser, $this->proxypwd], 'timeout' => 6]);
                $this->captcha = json_decode($questions->body, true);
                // $this->info('https://passport.yandex.ru/registration-validations/textcaptcha...' . json_encode($this->captcha));
                if (empty($this->captcha['image_url'])) {
                    $this->info('获取验证码失败第' . $i . '失败');
                    continue;
                }
            } catch (Throwable $t) {
                $this->info('获取验证码失败第' . $i . '失败' . $t->getMessage());
                continue;
            }
        }

        if (empty($this->captcha['image_url'])) {
            $this->info('获取验证码失败...');
            $this->error = '获取验证码失败';
            return;
        }
        return $this->captcha['image_url'];
    }

    public function getQuestions()
    {
        $this->info('获取问题');
        sleep(2);

        $parameters = [
            'track_id'   => $this->track_id,
            'csrf_token' => $this->csrf_token,
        ];
        $questions       = Requests::post('https://passport.yandex.ru/registration-validations/getQuestions', $this->getAjaxHead(), $parameters, ['cookies' => $this->cookies, 'proxy' => [$this->proxy, $this->proxyuser, $this->proxypwd]]);
        $this->questions = json_decode($questions->body);
        $this->info('https://passport.yandex.ru/registration-validations/getQuestion...');
        return $this->questions;
    }
    public function getCookies()
    {
        $this->cookies = new Requests_Cookie_Jar();
        $response      = Requests::get('https://login.inbox.lv/signup?go=portal', [], ['cookies' => $this->cookies, 'proxy' => [$this->proxy, $this->proxyuser, $this->proxypwd]]);
        // $body          = $response->body;

        // preg_match('/name=\"track_id\" value=\"(.*?)\"/', $body, $match);
        // //preg_match('/name=\"track_id\" value=\"(.*)\"\/><svg width=\"0\"/', $body, $match);
        // $this->track_id = $match[1];

        // preg_match('/data-csrf=\"(.*)\" data-metrics-id/', $body, $match);
        // $this->csrf_token = $match[1];

        // $this->info('cookies:' . json_encode($this->cookies));
        // $this->info('track_id:' . $this->track_id);
        // $this->info('csrf_token:' . $this->csrf_token);
        // if (!$this->track_id || !$this->csrf_token) {
        //     $this->info('...失败]]]');
        //     return $this->error = '失败';
        // }

        return;
    }

    public function getTime()
    {
        $time = explode(" ", microtime());
        $time = $time[1] . ($time[0] * 1000);
        $time = explode(".", $time);
        return $time[0];
    }
    public function getHeader()
    {
        $proxy = [
            'host' => '118.31.212.185:14280',
            'user' => 'cn_xs',
            'pwd'  => 'Al0MF4fizqjbM9Ql',
        ];

        $uniqid = $this->uniqid();
        $time   = time();
        $pwd    = sprintf('did=%s&uid=%s&pid=%d&cid=%d&uuid=%s&t=%d', 'did', $uniqid, -1, -1, $uniqid, $time);
        $ser    = sprintf('did=%s&uid=%s&pid=%d&cid=%d&uuid=%s&t=%d&key=%s', 'did', $uniqid, -1, -1, $uniqid, $time, $proxy['pwd']);
        $sign   = $pwd . '&sign=' . md5($ser);
        // echo '1';
        // echo (md5($ser)).PHP_EOL;
        //  echo trim($sign);exit();
        $this->proxy     = $proxy['host'];
        $this->proxyuser = $proxy['user'];
        $this->proxypwd  = trim($sign);

        // return $header[mt_rand(0, count($header) - 1)];
        return [
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent'                => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36',
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Encoding'           => 'gzip, deflate, sdch',
            'Accept-Language'           => 'zh-CN,zh;q=0.8,en;q=0.6',
            'Connection'                => 'keep-alive',
            'Content-Type'              => 'application/x-www-form-urlencoded',
            'Cache-Control'             => 'no-cache',
            'X-Requested-With'          => 'XMLHttpRequest',
        ];

    }

    public function uniqid()
    {
        static $guid = '';
        $this->info('进程ID：' . $this->pid);
        $uid  = uniqid($this->pid, true);
        $data = 'xiaozi123';

        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = substr($hash, 0, 8) .
        '-' .
        substr($hash, 8, 4) .
        '-' .
        substr($hash, 12, 4) .
        '-' .
        substr($hash, 16, 4) .
        '-' .
        substr($hash, 20, 12);
        return $guid;
    }

    public function getStartHead()
    {
        return [
            "Accept"          => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
            "Accept-Encoding" => "gzip, deflate, br",
            "Accept-Language" => "zh-CN,zh;q=0.9,en;q=0.8",
            "User-Agent"      => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36",
        ];
    }

    public function getAjaxHead()
    {
        return [
            "Accept"           => "application/json, text/javascript, */*; q=0.01",
            "Accept-Encoding"  => "gzip, deflate, br",
            // "Accept-Language" => "zh-CN,zh;q=0.9,en;q=0.8",
            // "Connection" => "keep-alive",
            // "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
            "User-Agent"       => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36",
            "X-Requested-With" => "XMLHttpRequest",
        ];
    }
}

function http($url, $params = [], $method = 'GET')
{
    if (!empty($params)) {
        $url = $url . "?" . http_build_query($query);
    }

    $info = [
        'http' => array(
            'method'  => $method,
            'header'  => 'Content-type: image/gif; charset=utf-8',
            'timeout' => '3',
        ),
    ];
    if ('POST' == $method) {
        $info['http']['content'] = http_build_query($params);
    }

    $res = 'error';
    try {
        $res = file_get_contents($url, 0, stream_context_create($info));
    } catch (\Exception $e) {
        return $res;
    }
    return $res;
}

function randName($floor = 7, $top = 20)
{
    $letters = ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', '.', 'G', 'H', 'J', 'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm'];
    $account = [];
    $val     = mt_rand($floor, $top);
    for ($i = 0; $i < $val; $i++) {
        $l         = $letters[mt_rand(0, count($letters) - 1)];
        $account[] = $l;
    }
    return implode('', $account);
}
