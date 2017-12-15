<?php

namespace App\Console\Commands\Data;

use App\App;
use App\Models\WorkDetail;
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
     * * @return mixed */
    public function handle()
    {
        $total_key = 'valid_account_ids';

        $offset       = 0;
        $max_email_id = Redis::get('email_max_id');
        do {
            $data = DB::table('emails')->select('id')->where('valid_status', 1)
            //->where('id','<',$max_email_id)
                ->offset($offset)
                ->orderBy('id', 'asc')
                ->limit(10000)
                ->get();
            $offset += 10000;
            if ($data->isEmpty()) {
                break;
            }
            foreach ($data as $key => $r) {
                Redis::sAdd('valid_account_ids', $r->id);
                if (!$key % 1000) {
                    echo $r->id . "\n";
                }
            }
            echo $offset . "\n";
        } while (1);
        $max_id = DB::table('emails')->max('id');
        Redis::set('email_max_id', $max_id);
        echo 'max_id:' . $max_id;
        echo 'valid_account_ids:size:' . Redis::sSize($total_key) . "\n";
        // die;
        //set work_detail account_id sort
       

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
