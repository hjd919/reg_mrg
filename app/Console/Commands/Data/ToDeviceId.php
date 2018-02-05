<?php

namespace App\Console\Commands\Data;

use App\Models\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class ToDeviceId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:redis {--offset=} {--max_offset=}';

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
	$offset = $this->option('offset');
	$max_offset = $this->option('max_offset');
        $i = 0;
        while (1) {
	    if($offset>$max_offset) break;

            // 抓取平台的验证码
            $url     = "http://www.chaojiying.cn/user/history/{$offset}/1/0/";
            $content = App::curl($url);

            // echo($content);
            $pattern = '#<td bgcolor="\#FFFFFF"><div align="center"><img src="(.+?)"\/><\/br>\W+图片ID.+?4005</div>.+?<div align="center">(.+?)</div></td>#sm';
            if (!preg_match_all($pattern, $content, $match)) {
                echo 'error';
		break;
            }
            $codes = $match[2];

	    $url_md5 = md5($url);
	    $dir = '/tmp/code_images/'.substr($url_md5,0,3).'/'.substr($url_md5,3,3); 
            if (!is_dir($dir)) {
                mkdir($dir,0777,true);
            }

	    if(!count($codes)) break;
	    
            echo "offset-{$offset}\n";
            foreach ($match[1] as $key => $image_url) {
                $code = $codes[$key];
                if (strlen($code) !== 5) {
                    continue;
                }
                $filename = "{$dir}/{$code}.gif";
                if (!file_exists($filename)) {
		    try{
                    	file_put_contents($filename, file_get_contents($image_url));
	    		$i++;
		    }catch(\Exception $e){
			continue;
		    }
                }
            }
	    $offset++;
        }
	file_put_contents('./code_res_'.$offset,$i);

        die;

        $appid = '1325424608';
        // $key = "useful_account_ids:appid_{$appid}";
        $key = "used_account_ids:appid_{$appid}";
        // $key = "valid_account_ids";
        // echo $key;
        $max_id = Redis::sSize($key);
        var_dump($max_id);
        die;

        Redis::set('work_table', 'works2');

        die;
        $data = Redis::delete('did_to_gid');
        var_dump($data);
        die;
        $data = Redis::sMembers('exist_brush_idfas_stat');
        print_r($data);
        die;
        // 邮箱
        $flag = Mail::raw('图片名为验证码结果', function ($message) {
            // $to = '297538600@qq.com';
            $to = '76608853@qq.com';
            $message->to($to)->subject('apple验证码图片');
            $filename   = 'checkcode_images.tar.bz2';
            $attachment = './' . $filename;
            //在邮件中上传附件
            $message->attach($attachment, ['as' => $filename]);
        });

        die;

        $apps_row = DB::table('apps')->where('is_brushing', '0')->where('create_time', '>', '2018-01-15')->get();

        for ($mobile_group_id = 1000; $mobile_group_id < 1008; $mobile_group_id++) {
            // 删除组id缓存
            $device_ids = DB::table('mobiles')->select('device_id')->where(['mobile_group_id' => $mobile_group_id])->pluck('device_id');
            if (!$device_ids) {
                echo 'mobile_id' . $app_row->mobile_group_id;
                continue;
            }
            foreach ($device_ids as $device_id) {
                Redis::hDel('did_to_gid', $device_id);
            }
        }

        die;

        $mobiles = DB::table('mobiles')->get();
        foreach ($mobiles as $mobile) {
            $device_id = $mobile->device_id;
            $mobile_id = $mobile->id;
            Redis::hSet("did_to_mid", $device_id, $mobile_id);
        }
        dd(count(Redis::hGetAll('did_to_mid')));
        die;
        /* $total_key  = 'valid_account_ids';
        $appids = Redis::sSize($total_key);
        dd($appids);
         */

        // 删除今天早上导入的账号ID
        /*$offset = 0;
        while(1){
        $ids = DB::table('emails')->where('create_time','>','2018-01-14')->where('create_time','<','2018-01-14 22:00:00')->orderBy('id','desc')->offset($offset)->limit(1000)->pluck('id');
        if(!$ids){
        break;
        }
        echo count($ids)."\n";
        foreach ($ids as $key => $account_id) {
        $res = Redis::sRem('valid_account_ids',$account_id);
        if(!$res){
        // 删除不聊
        break;
        }else{
        echo $key."\n";
        }
        }
        $offset += 1000;
        }
         */
        echo "更新可用账号数量\n";
        $appids = Redis::sMembers('account_policy_2');
        foreach ($appids as $appid) {
            $total_key  = 'valid_account_ids';
            $sort_key   = "used_account_ids:appid_{$appid}";
            $useful_key = "useful_account_ids:appid_{$appid}";
            echo '清除旧集合' . var_dump(Redis::delete($useful_key)) . "\n"; // 先清除旧集合
            var_dump(Redis::sDiffStore($useful_key, $total_key, $sort_key)) . "\n";
        }

        die;
        // 更新假设备机器
        for ($i = 1011; $i <= 1012; $i++) {
            $res = DB::table('mobiles')->where('mobile_group_id', 0)->where('is_normal', 1)->limit(4)->update(['mobile_group_id' => $i]);
            if ($res) {
                echo $i . "\n";
            }
        }
        die;
        // 迁移redis数据
        $redis  = Redis::connection();
        $redis2 = Redis::connection('test');

        $ios_apps = DB::table('ios_apps')->get();
        // $key1     = "is_new_email:appid_{$appid}";
        // $key2     = 'last_email_id:appid_' . $appid;
        // $key3     = 'last_device_id:appid_' . $appid;
        foreach ($ios_apps as $ios_app) {
            $appid = $ios_app->appid;
            $key1  = "is_new_email:appid_{$appid}";
            $key2  = 'last_email_id:appid_' . $appid;
            $key3  = 'last_device_id:appid_' . $appid;

            if (!$redis->get($key2)) {
                continue;
            }
            $redis2->set($key1, $redis->get($key1));
            $redis2->set($key2, $redis->get($key2));
            $redis2->set($key3, $redis->get($key3));

            $val  = $redis2->get($key1);
            $val2 = $redis2->get($key2);
            $val3 = $redis2->get($key3);

            echo json_encode(compact('appid', 'val', 'val2', 'val3')) . "\n";
        }

        $work_detail_table = $redis->hGetAll('work_detail_table');
        print_r($work_detail_table);
        $redis2->hMSet('work_detail_table', $work_detail_table);
        $work_detail_table = $redis2->hGetAll('work_detail_table');
        print_r($work_detail_table);
        die;
        // // * to device_id
        // $i = 0;
        // while (1) {
        //     $rows = DB::table('work_detail')->select('id', 'udid')->where('device_id', 0)->limit(1000)->get();
        //     if ($rows->isEmpty()) {
        //         break;
        //     }

        //     foreach ($rows as $row) {
        //         $device_id = DB::table('devices')->select('id')->where('udid', $row->udid)->value('id');
        //         $res       = DB::table('work_detail')->where('id', $row->id)->update(['device_id' => $device_id]);
        //         if (!$res) {
        //             echo '更新失败';
        //         }
        //     }
        //     $i++;
        //     echo '执行' . $i . '次' . "\n";
        // }
    }
}
