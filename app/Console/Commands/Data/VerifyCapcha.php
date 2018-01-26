<?php

namespace App\Console\Commands\Data;

use App\App;
use HJD\Requests;
use Illuminate\Console\Command;

class VerifyCapcha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:capcha';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $try_times = 0;
        $capcha    = '';
        do {
            $source    = './casperjs/capture/capcha.png'; // 验证码截图
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

        // 没有验证码返回error
        if(!$capcha){
            echo 'error';
        }

        echo $capcha;
    }
}
