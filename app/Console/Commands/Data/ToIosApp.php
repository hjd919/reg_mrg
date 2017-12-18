<?php

namespace App\Console\Commands\Data;

use App\App;
use App\Models\WorkDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ToIosApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sAdd:used_account_ids {--appid=1141755797}';

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
        // $res = Redis::sAdd('account_policy_2','1211055336');
        // $res1 = Redis::sAdd('account_policy_2','1141755797');
        // if (Redis::sIsMember('account_policy_2', '1141755797')) {
        //     echo 'ok';
        // }else{
        //     echo 'fail';
        // }
        // die;
        // $appid = '1141755797';
        // // $key ="useful_account_ids:appid_{$appid}";
        // // $key   = "used_account_ids:appid_{$appid}";
        // $key = "used_account_ids:appid_{$appid}";
        // // $total_key = 'valid_account_ids';
        // // // var_dump(Redis::sDiffStore("useful_account_ids:appid_{$appid}", $total_key, $used_account_ids_key));
        // $num = WorkDetail::getWorkDetailTable($appid)->where('appid', $appid)->count();
        // echo $num . "\n";
        // // // $key = 'valid_account_ids';
        // echo Redis::sSize($key) . "\n";
        // // var_dump(Redis::sIsMember($key, '1592129')) . "\n";
        // die;
        // 已用过账号
        // $appid    = '1141755797';
        $appid = $this->option('appid');

        $sort_key = "used_account_ids:appid_{$appid}";
        $offset   = 10000;
        $j        = $i        = 0;
        while (1) {
            $data = WorkDetail::getWorkDetailTable($appid)->select('account_id')->where('appid', $appid)->groupBy('account_id')->orderBy('account_id', 'asc')->offset($offset)->limit(10000)->get();
            if ($data->isEmpty()) {
                break;
            }
            echo 'offset-' . $offset . "\n";
            $offset += 10000;
            foreach ($data as $key => $r) {
                $i++;
                $res = Redis::sAdd($sort_key, $r->account_id);
                if ($res) {
                    $j++;
                }
            }
        }
        echo Redis::sSize($sort_key) . "\n";
        echo "执行了多少次--{$i}--{$j}\n";
        // die;
        // diff two sort
        // 某个时间点未用过账号
        $total_key  = 'valid_account_ids';
        $sort_key   = "used_account_ids:appid_{$appid}";
        $useful_key = "useful_account_ids:appid_{$appid}";
        var_dump(Redis::delete($useful_key)) . "\n";
        var_dump(Redis::sDiffStore($useful_key, $total_key, $sort_key)) . "\n";
        echo Redis::sSize($useful_key) . "\n";
    }
}
