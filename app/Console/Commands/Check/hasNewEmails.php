<?php

namespace App\Console\Commands\Check;

use App\Models\App;
use App\Models\Email;
use App\Models\WorkDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class hasNewEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:has_new_emails';

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
        // 判断当前跑的app是否有新邮箱了，有则先跑新邮箱
        // 获取当前跑的app
        $apps = App::where('is_brushing', 1)->groupBy('appid')->get();
        if ($apps->isEmpty()) {
            return true;
        }

        $max_email_id = Email::where('valid_status', 1)->max('id');

        foreach ($apps as $app) {
            $appid = $app->appid;

            // 判断是否有新邮箱
            $is_new_email = Redis::get("is_new_email:appid_{$appid}");
            if (!$is_new_email) {
                $max_account_id = WorkDetail::getMaxAccountId($appid);
                if ($max_account_id < $max_email_id) {
                    // echo json_encode(compact('is_new_email', 'appid', 'max_email_id', 'max_account_id')) . "\n";
                    // continue;
                    // 有新邮箱，优先跑新邮箱
                    // 1.1 标志在跑新邮箱
                    Redis::set("is_new_email:appid_{$appid}", 1);
                    // 1.2 更新新账号的max_id，min_id
                    $max_account_id = WorkDetail::getMaxAccountId($appid);
                    $min_account_id = WorkDetail::getMinAccountId($appid);
                    DB::table('ios_apps')->where('appid', $appid)->update(compact('max_account_id', 'min_account_id'));
                    // 1.3 更新last_id为最大id
                    Redis::set(Email::get_last_id_key($appid), 99999999999);
                }
            }
        }
    }
}
