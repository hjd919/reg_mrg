<?php

namespace App\Console\Commands\Data;

use App\App;
use App\Models\WorkDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RevertEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revert:email {--appid=1141755797}';

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
        $appid = $this->option('appid');
        
        $used_account_ids_key = "used_account_ids:appid_{$appid}";
        $appids = Redis::sSize($used_account_ids_key);
        dd($appids);
        // $appids = Redis::sMembers('account_policy_2');
        // dd($appids);
        
        $appid = $this->option('appid');
        if (!$appid) {
            return false;
        }
        $offset = 0;
        $step   = 2000;
        for ($i = 0; $i < 1; $i++) {
            // 判断是否策略二
            $is_policy_2 = Redis::sIsMember('account_policy_2', $appid);
            if ($is_policy_2) {

                // 获取错误可以重复使用的任务记录
                $re_uses = WorkDetail::getWorkDetailTable($appid)
                    ->select('account_id')
                    ->where('appid', $appid)
                    ->whereNotIn('fail_reason', [0, 13, 14, 15])
                    ->offset($offset)
                    ->limit($step)
                    ->pluck('account_id');
                if (!$re_uses) {
                    break;
                }

                // 删除缓存中已用账号
                foreach ($re_uses as $account_id) {
                    $used_account_ids_key = "used_account_ids:appid_{$appid}";
                    Redis::sRemove($used_account_ids_key, $account_id);
                }

            }

            // 删除错误不为14的记录
            WorkDetail::getWorkDetailTable($appid)
                ->where('appid', $appid)
                ->update(['status'=>9]);
        }

    }
}
