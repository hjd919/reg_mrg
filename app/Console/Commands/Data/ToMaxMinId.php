<?php

namespace App\Console\Commands\Data;

use App\App;
use App\Models\WorkDetail;
use App\Support\Util;
use HJD\Requests;
use HJD\Selector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ToMaxMinId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'to:max_min_id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    const RAND_STR = 'qwertyuiopasdfghjklzxcvbnm123456789';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    // 获取随机字符串
    public function get_rand_str()
    {
        return substr(self::RAND_STR, rand(0, 25), rand(1, 10));
    }

    public function save_capture()
    {
        $capture_url   = "https://c.mail.ru/c/6";
        $response_data = Requests::get($capture_url); // 下载文件
        return file_put_contents('./6.jpeg', $response_data);
    }

    public function upload_capture()
    {
        $try_times = 0;
        $capcha    = '';
        do {
            $source    = './6.jpeg';
            $file      = new \CURLFile(realpath($source));
            $dama_url  = "http://api.yundama.com/api.php";
            $username  = '875486058';
            $password  = 'xz123456789';
            $codetype  = '1006';
            $appid     = '4205';
            $timeout   = 30;
            $appkey    = '7eeaeddab5e3c288d88733f603eee88d';
            $method    = 'upload';
            $post_data = compact(
                'username',
                'password',
                'codetype',
                'appid',
                'appkey',
                'timeout',
                'method',
                'file',
                'appid'
            );
            $response = Requests::post($dama_url, $post_data); // 上传文件TODO
            $response = json_decode($response, true);
            $try_times++;
            if ($response['ret']) {
                continue;
            }
            $capcha = $response['text'];
            if ($capcha) {
                break;
            }
        } while ($try_times < 10);

        return $capcha;
    }

    public function get_page_input()
    {
        $page_url = "https://account.mail.ru/signup/verify";
        $html     = Requests::get($page_url);
        Util::log('html', $html);
        $id = Selector::select($html, "//input[@name='id']@value");
        Util::log('id_value', $id);
        return $id;
    }

    /**
     * Execute the console command.
     * * @return mixed */
    public function handle()
    {
        // 注册.ru邮箱
        $signup_url = "https://account.mail.ru/api/v1/user/signup";
    
        $name = json_encode([
            'first' => $this->get_rand_str(),
            'last'  => $this->get_rand_str(),
        ]);
        $from     = '';
        $sex      = 'male';
        $birthday = json_encode([
            'day'   => rand(1, 30),
            'month' => rand(1, 11),
            'year'  => rand(1970, 2005),
        ]);
        $context     = 'signup';
        $browser     = '{"screen":{"availWidth":"1235","availHeight":"777","width":"1280","height":"800","colorDepth":"24","pixelDepth":"24","availLeft":"45","availTop":"23"},"navigator":{"vendorSub":"","productSub":"20030107","vendor":"Google Inc.","maxTouchPoints":"0","hardwareConcurrency":"4","cookieEnabled":"true","appCodeName":"Mozilla","appName":"Netscape","appVersion":"5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36","platform":"MacIntel","product":"Gecko","userAgent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36","language":"zh-CN","onLine":"true","doNotTrack":"inaccessible","deviceMemory":"8"},"flash":{"version":"inaccessible"}}';
        $device      = '{"os":"","os_version":"","dtid":"","viewType":"0"}';
        $login       = $this->get_rand_str() . rand(1000, 9000) . $this->get_rand_str();
        $domain      = 'mail.ru';
        $password    = 'hujiande123';
        $htmlencoded = 'false';
        $post_data   = compact(
            'name',
            'from',
            'sex',
            'birthday',
            'context',
            'browser',
            'login',
            'domain',
            'password',
            'htmlencoded',
            'device'
        );


        $daili_ips = file_get_contents('http://api.goubanjia.com/api/get.shtml?order=d3473818fc66ccf7b5a2548b4966fcd4&num=100&area=%E9%9D%9E%E4%B8%AD%E5%9B%BD&carrier=0&protocol=2&an1=1&an2=2&sp1=1&sp2=2&sp3=3&sort=1&system=1&distinct=0&rettype=0&seprator=%0D%0A');
        $daili_ips = json_decode($daili_ips, true);

        $ua = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
        Requests::set_useragent($ua);
        // $res = Requests::get('http://2017.ip138.com/ic.asp');
        // echo $res;
        // die;
        // 初始化 获取cookie
        $ip_rows = $daili_ips['data'];
        $ip_count = count($ip_rows);
        for ($i = 0; $i < $ip_count; $i++) {
            $page_url = "https://account.mail.ru/signup/simple";
            // $page_url = "https://www.baidu.com";
            $ip_data  = $ip_rows[$i];
            if($ip_data['type'] !='https'){
                continue;
            }
            // 设置代理
            $proxies = [
                // 'http'  => 'socks5://user:pass@104.224.154.15:22',
                // 'https'  => 'socks5://user:pass@104.224.154.15:22',
                // 'http' => '103.201.140.10:53281',
                // 'http'  => $ip_data['ip'] . ':' . $ip_data['port'],
                // 'https' => $ip_data['ip'] . ':' . $ip_data['port'],
                'https' => '217.61.1.157:20188',
            ];
            // print_r($proxies);
            // Requests::set_proxies($proxies);

            $html = Requests::get($page_url);
            break;
            if ($html) {
                break;
            }
        }
        if(!$html){
            die('代理失败');
        }
     
        $img_src = Selector::select($html, '#<img src="(.+?)"#', "regex");
        $id      = 0;
        foreach ($img_src as $img_url) {
            if (strpos($img_url, 'logo2x.png') !== false) {
                continue;
            }
            if (preg_match('#id=(\d+);#', $img_url, $match)) {
                $id = $match[1];
            }
            $img_url = strpos($img_url, 'http') === false ? 'http:' . $img_url : $img_url;
            Requests::get($img_url);
        }
        $time      = time() . rand(100, 999);
        $sid       = substr(md5(time()), 0, 16);
        $login_url = "https://top-fwz1.mail.ru/tracker?js=13;id={$id};u=https%3A//account.mail.ru/signup;st={$time};s=1280*800;vp=1219*150;touch=0;hds=2;flash=;sid={$sid};ver=60;nt=1/0/1514041827321/2063/2063///11/11/11/12/1011/396/1011/2055/2699/2074/4864/4864/4876/28451/28451/;detect=0;_=0.3357009722652897;e=RT/load;et={$time}";
        Requests::get($login_url);
        $login_url = "https://top-fwz1.mail.ru/tracker?js=13;id={$id};e=RT/beat;sid={$sid};ids={$id};ver=60;_=0.9202775536003398";
        Requests::get($login_url);
        // 简单密码
        Requests::get("https: //top-fwz1.mail.ru/tracker?js=13;id={$id};u=https%3A//account.mail.ru/signup/simple;r=https%3A//mail.ru/;st={$time};title=%D0%A0%D0%B5%D0%B3%D0%B8%D1%81%D1%82%D1%80%D0%B0%D1%86%D0%B8%D1%8F;s=1280*800;vp=1219*184;touch=0;hds=2;flash=;sid={$sid};ver=60;detect=0;_=0.6272288431324511;e=PVT/15");
        // 注册一
        Util::log('post_data', $post_data);
        $response_data = Requests::post($signup_url, $post_data);
        Util::log('注册一response_data', $response_data);
        if (!$response_data
            || !($response_data = json_decode($response_data, true))
            || !isset($response_data['body'])
            || $response_data['status'] == 400) {
            Util::log('注册一失败', $response_data);
            return false;
        }

        // # 注册二 下载验证码，获取验证码，并填写提交

        // 获取$id值 TODO
        $id       = $response_data['body'];
        $try_time = 0;
        do {

            // 获取$capcha值 TODO
            //保存验证码图片
            $res = $this->save_capture();
            if (!$res) {
                Util::log('保存验证码图片失败');
                return false;
            }

            // 提交打码平台
            $capcha = $this->upload_capture();
            if (!$capcha) {
                Util::log('打码失败');
                return false;
            }

            $email        = $login . '@' . $domain;
            $from         = '';
            $reg_anketa   = json_encode(compact("id", "capcha"));
            $redirect_uri = 'https://e.mail.ru/messages/inbox?newreg=1&signup_b=1&sms_reg=1&features=1';
            $htmlencoded  = 'false';

            // 提交数据
            $post_data = compact('confirm_url', 'email', 'from', 'reg_anketa', 'redirect_uri', 'htmlencoded');
            Util::log('注册2-post_data', $post_data);
            $confirm_url   = "https://account.mail.ru/api/v1/user/signup/confirm";
            $response_data = Requests::post($confirm_url, $post_data);
            Util::log('注册2-response_data', $response_data);
            if (strpos($response_data, '{"value":"' . $capcha . '","error":"invalid"}') !== false) {
                // 打码失败，重打
            } else {
                break;
            }
            $try_time++;
        } while ($try_time < 1);
        // unlink('./6.jpeg');
        file_put_contents('./cookie.txt', ''); // 清空cookie内容

        die;
        $app = DB::table('apps')->whereColumn('brush_num', 'success_num')->where('is_brushing', 0)->where('brushed_num', '>', 0)->where('create_time', '>', '2017-12-10')->get();
        echo count($app->toArray()) . "\n";
        foreach ($app as $key => $app_row) {
            $res = DB::table('apps')->where('id', $app_row->id)->update(['brush_num' => $app_row->success_num - $app_row->success_brushed_num]);
            if ($res) {
                echo $key . "\n";
            } else {
                echo $key . "fail\n";
            }
            echo $app_row->id . "-" . $app_row->appid . "\n";
        }
        die;

        file_put_contents('tomax.txt', date('Ymd_H:i:s'), FILE_APPEND);
        sleep(40);
        file_put_contents('tomax.txt', date('Ymd_H:i:s'), FILE_APPEND);
        die;
        $total_key = 'valid_account_ids';

        $offset = 0;
        // $max_email_id = Redis::get('email_max_id');
        do {
            $data = DB::table('emails')->select('id')->where('valid_status', 1)
            //->where('id','<',$max_email_id)
                ->offset($offset)
                ->orderBy('id', 'asc')
                ->limit(10000)
                ->get();
            $offset += 10000;
            echo $offset . "\n";
            if ($data->isEmpty()) {
                break;
            }
            foreach ($data as $key => $r) {
                Redis::sAdd('valid_account_ids', $r->id);
            }
            echo $offset . "\n";
        } while (1);
        $max_id = DB::table('emails')->max('id');
        Redis::set('email_max_id', $max_id);
        echo 'max_id:' . $max_id;
        echo 'valid_account_ids:size:' . Redis::sSize($total_key) . "\n";
        die;
        // die;
        //set work_detail account_id sort
        $total_key = 'valid_account_ids';
        $appid     = '1211055336';
        $sort_key  = "used_account_ids:appid_{$appid}";
        $offset    = 10000;
        while (1) {
            $data = WorkDetail::getWorkDetailTable($appid)->select('account_id')->where('appid', $appid)->offset($offset)->limit(10000)->get();
            if ($data->isEmpty()) {
                break;
            }
            echo $offset . "\n";
            $offset += 10000;
            foreach ($data as $r) {
                Redis::sAdd($sort_key, $r->account_id);
                // echo $r->account_id . "\n";
            }
        }
        // diff two sort
        var_dump(Redis::sDiffStore("useful_account_ids:appid_{$appid}", $total_key, $sort_key)) . "\n";
        echo Redis::sSize("valid_account_id:appid_{$appid}");
        die;

        //     // * to device_id
        //     $rows = DB::table('apps')->groupBy('appid')->get();
        //     if ($rows->isEmpty()) {
        //         return false;
        //     }
        //     $i = 0;
        //     foreach ($rows as $row) {
        //         $appid = $row->appid;

        //         // 获取last_id
        //         $key     = Email::get_last_id_key($appid);
        //         $last_id = Redis::get($key);
        //         if (!$last_id) {
        //             continue;
        //         }
        //         // 判断是否异常情况
        //         $min_account_id = WorkDetail::getMinAccountId($appid);
        //         $max_account_id = WorkDetail::getMaxAccountId($appid);
        //         if ($last_id > $min_account_id && $last_id < $max_account_id) {

        //             // 设置最大id= db<last_id max account_id
        //             $new_max_account_id = WorkDetail::getWorkDetailTable($appid)->where('account_id', '<', $last_id)->max('account_id');
        //             $res1               = DB::table('ios_apps')->where('appid', $appid)->update([
        //                 'max_account_id' => $new_max_account_id,
        //             ]);
        //             echo json_encode([
        //                 'appid'              => $appid,
        //                 'last_id'            => $last_id,
        //                 'min_account_id'     => $min_account_id,
        //                 'max_account_id'     => $max_account_id,
        //                 'new_max_account_id' => $new_max_account_id,
        //             ]) . "\n";

        //             // x 设置last_id=最小id

        //             // 标志在刷新账号
        //             $res2 = Redis::set("is_new_email:appid_{$appid}", 1);

        //             if ($res1 && $res2) {
        //                 echo '成功', "\n";
        //             } else {
        //                 echo '失败', "\n";
        //             }
        //         }
        //         $i++;
        //     }

        //     echo "执行了{$i}次";
    }
}
