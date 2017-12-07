<?php

namespace App\Console\Commands\Check;

use App\App;
use Illuminate\Console\Command;

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
        $apps = App::where('is_brushing', 1)->get();
        if ($apps->isEmpty()) {
            return true;
        }

        foreach ($apps as $app) {
            $appid = $app->appid;

        }

        // 判断是否还剩1000了，则需要导入邮箱
        $total = DB::table('appleids')->where('state', 0)->count();
        if ($total < 1000) {
            // 邮箱通知
            $msg    = '需要添加注册苹果账号的邮箱了';
            $toMail = 'yanjie@xiaozi.com.cn';
            $cc     = ['297538600@qq.com', 'tianshaokun@xiaozi.com.cn'];
            Mail::raw($msg, function ($message) use ($toMail, $cc) {
                $message->subject('需要添加注册苹果账号的邮箱了');
                $message->to($toMail);
                $message->cc($cc);
            });

        }
    }
}
