<?php

namespace App\Console\Commands\CronTask;

use App\Models\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ScpImg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scp:img';

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
        @rmdir('./code_images');
        mkdir('./code_images');

        for ($i = 1; $i < 100; $i++) {
            // 抓取平台的验证码
            $url     = "http://www.chaojiying.cn/user/history/{$i}/1/0/";
            $content = App::curl($url);

            // echo($content);
            $pattern = '#<td bgcolor="\#FFFFFF"><div align="center"><img src="(.+?)"\/><\/br>\W+图片ID.+?4005</div>.+?<div align="center">(.+?)</div></td>#ism';
            if (!preg_match_all($pattern, $content, $match)) {
                echo 'error';
                
                // 需要重新登录了
                Mail::raw('需要重新登录了', function ($message) {
                    $message->subject('抓取验证码图片需要重新登录');
                    $message->to('297538600@qq.com');
                });
                break;
            }
            $codes = $match[2];
            
            // echo "i-{$i};codes_size-" . count($codes) . "\n";
            foreach ($match[1] as $key => $image_url) {
                $code = $codes[$key];
                if (strlen($code) !== 5) {
                    continue;
                }
                $filename = "./code_images/{$code}.gif";
                if (!file_exists($filename)) {
                    file_put_contents($filename, file_get_contents($image_url));
                }
            }
        }

        // scp文件
        // exec("scp code_images root@120.26.75.163:/var/lib/docker/moredata");

    }
}
