<?php
namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Throwable;

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

class RegMail extends Command
{

    protected $signature   = 'reg:mail';
    protected $description = '';
    private $header;
    private $cookies;
    private $captcha;
    private $questions;
    private $track_id;
    private $capcha;
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

    private $client;

    private $pid;

    public function handle()
    {
        $this->setHeader();
        $this->setProxy();
        $this->setClient();
        // try {
        $this->info('[[[[开始...');
        $this->setCookies();
        $this->info('生成账号信息..');
        $this->genAccountInfo();
        $this->getCaptcha();
        if (!$this->textCaptcha()) {
            return;
        }
        if (!$this->checkCaptcha()) {
            return;
        }

        // $this->finishInfo();

        // } catch (Throwable $t) {
        //     echo $t->getMessage() . PHP_EOL;
        //     $this->info($t->getMessage());
        //     return;
        // }
    }

    protected function setHeader()
    {
        $this->header = [
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent'                => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36',
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Encoding'           => 'gzip, deflate, sdch',
            'Accept-Language'           => 'zh-CN,zh;q=0.8,en;q=0.6',
            'Connection'                => 'keep-alive',
            'Content-Type'              => 'application/x-www-form-urlencoded',
            'Cache-Control'             => 'no-cache',
            'X-Requested-With'          => 'XMLHttpRequest',
            'Referer'                   => 'https: //login.inbox.lv/signup?go=portal',
        ];
    }

    protected function setProxy()
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

    public function info($info, $verbosity = null)
    {
        $msg = '>>>>' . $info . '  ' . date('Y-m-d H:i:s') . PHP_EOL;
        echo $msg;
        // file_put_contents('./logs/' . date('Y-m-d') . $this->pid . '.log', $msg, FILE_APPEND);
    }

    public function setCookies()
    {
        $this->client->request('GET', 'https://login.inbox.lv/signup?go=portal');
        return;
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
                // sleep(1);
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
            'userpin'  => '',
            'username' => $account,
        ];
        $response = $this->client->request('POST', 'https://login.inbox.lv/signup/check_username', [
            'body' => \http_build_query($parameters),
        ]);

        // $response = Requests::post('https://passport.yandex.ru/registration-validations/login', $this->getAjaxHead(), $parameters, ['cookies' => $this->cookies, 'proxy' => [$this->proxy, $this->proxyuser, $this->proxypwd]]);
        $json = json_decode($response->getBody(), true);
        if (empty($json) || empty($json['flash']) || $json['flash']['type'] != 'success') {
            return false;
        }
        return true;
    }

    public function getCaptcha()
    {
        $this->info('获取验证码');
        $parameters = [
            'track_id'   => $this->track_id,
            'csrf_token' => $this->csrf_token,
            'language'   => 'ru',
        ];

        $is_success = false;
        for ($i = 0; $i < 5; $i++) {
            try {
                $url      = "https://login.inbox.lv/captcha2?namespace=signup&" . substr(md5(time()), 0, 14);
                $response = $this->client->request('GET', $url, [
                    'headers' => [
                        'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.80 Safari/537.36',
                        'Accept'          => 'image/webp,image/apng,image/*,*/*;q=0.8',
                        'Referer'         => 'https://login.inbox.lv/signup?go=portal',
                        'Accept-Encoding' => 'gzip, deflate, br',
                    ],
                ]);
                // $questions     = Requests::post('https://passport.yandex.ru/registration-validations/textcaptcha', $this->getAjaxHead(), $parameters, ['cookies' => $this->cookies, 'proxy' => [$this->proxy, $this->proxyuser, $this->proxypwd], 'timeout' => 6]);
                // $body = $questions->getBody();
                if ($response->getStatusCode() == 200) {
                    $is_success = true;
                    file_put_contents(\storage_path('app/test.png'), $response->getBody());
                    break;
                }
                // $this->captcha = json_decode($questions->getBody(), true);
                // $this->info('https://passport.yandex.ru/registration-validations/textcaptcha...' . json_encode($this->captcha));
                // if (empty($this->captcha['image_url'])) {
                $this->info('获取验证码失败第' . $i . '失败');
                continue;
                // }
            } catch (Throwable $t) {
                $this->info('获取验证码失败第' . $i . '失败' . $t->getMessage());
                continue;
            }
        }

        if (!$is_success) {
            $this->info('获取验证码失败...');
            $this->error = '获取验证码失败';
            return;
        }
        return true;
    }

    public function textCaptcha()
    {
        $file     = storage_path('app/test.png');
        $url      = "http://api.yundama.com/api.php";
        $username = '875486058';
        $password = 'xz123456789';
        $codetype = '3005';
        $appid    = '4205';
        $timeout  = 20;
        $appkey   = '7eeaeddab5e3c288d88733f603eee88d';
        $method   = 'upload';

        for ($i = 0; $i < 5; $i++) {
            $post_data = [
                [
                    'name'     => 'username',
                    'contents' => $username,
                ],
                [
                    'name'     => 'password',
                    'contents' => $password,
                ],
                [
                    'name'     => 'codetype',
                    'contents' => $codetype,
                ],
                [
                    'name'     => 'appid',
                    'contents' => $appid,
                ],
                [
                    'name'     => 'appkey',
                    'contents' => $appkey,
                ],
                [
                    'name'     => 'timeout',
                    'contents' => $timeout,
                ],
                [
                    'name'     => 'method',
                    'contents' => $method,
                ],
                [
                    'name'     => 'file',
                    'contents' => fopen($file, 'r'),
                    'filename' => 'test.png',
                ],
            ];

            $response = $this->client->request('POST', $url, [
                'multipart' => $post_data,
            ]);

            $body = $response->getBody();
            $this->info($body);
            $json = json_decode($body, true);

            if ($json['ret']) {
                $this->info('解析验证码失败' . $i);
                sleep(3);
                continue;
            }
            $capcha = $json['text'];
            if ($capcha) {
                $this->capcha = $capcha;
                break;
            }
            $this->info('解析验证码空' . $i);
            sleep(3);
        }

        if ($this->capcha) {
            $this->info('验证码:' . $capcha);
            return true;
        }
        $this->info('解析验证码失败！');
        return false;
    }

    public function setClient()
    {
        $this->client = new Client([
            'cookies' => true,
            'debug'   => true,
            // 'request.options' => [
            'proxy'   => "https://cn_xs:{$this->proxypwd}@118.31.212.185:14280",
            // 'proxy'   => "https://cn_xs:{$this->proxypwd}@118.31.212.185:14280",
            'headers' => $this->header,
            // ],
        ]);
    }

    public function checkCaptcha()
    {
        $capcha   = $this->capcha;
        $response = $this->client->request('POST', 'https://login.inbox.lv/captcha/check', [
            'form_params' => [
                'userpin'   => $capcha,
                'namespace' => 'signup',
                'iframe'    => 'false',
            ],
        ]);
        $json = json_decode($response->getBody(), true);
        if (!$json['captchaCorrect']) {
            $this->info('验证验证码失败');
            return false;
        }
        $this->info('验证码正确');
        return true;
    }

    public function finishInfo()
    {

    }
}
