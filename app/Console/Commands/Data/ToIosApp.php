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
    protected $signature = 'export_delete:invalid_email';

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
        // $appid    = '1211055336';
        // $key  = "used_account_ids:appid_{$appid}";
        // // $key = "useful_account_ids:appid_{$appid}";
        // // $key = 'valid_account_ids';
        // echo Redis::sSize($key);
        // die;
        // 已用过账号
        $appid    = '1211055336';
        $sort_key = "used_account_ids:appid_{$appid}";
        $offset   = 10000;
        while (1) {
            $data = WorkDetail::getWorkDetailTable($appid)->select('account_id')->where('appid', $appid)->offset($offset)->limit(10000)->get();
            if ($data->isEmpty()) {
                break;
            }
            echo 'offset-' . $offset . "\n";
            $offset += 10000;
            foreach ($data as $key => $r) {
                $res = Redis::sAdd($sort_key, $r->account_id);
            }
        }
        echo Redis::sSize($sort_key)."\n";
        // die;
// diff two sort
        // 某个时间点未用过账号
        $total_key = 'valid_account_ids';
        $sort_key  = "used_account_ids:appid_{$appid}";
        var_dump(Redis::sDiffStore("useful_account_ids:appid_{$appid}", $total_key, $sort_key)) . "\n";
        echo Redis::sSize("useful_account_ids:appid_{$appid}")."\n";
        die;

        // 导出并删除失效账号

        // 导出
        $date = date('ymd');
        $code = exec("mysqldump -u'super_hjd' -p'Dev~!@#Hjd919' -P3306 --default-character-set=utf8 --no-create-db --no-create-info --tables jishua emails --where='valid_status=0' > storage/app/backup/jishua_emails_{$date}.table.sql");

        // 删除
        $res = DB::table('emails')->where('valid_status', 0)->delete();
        echo ('$res---');
        var_dump($res);

    }
}
