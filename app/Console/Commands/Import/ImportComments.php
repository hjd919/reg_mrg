<?php

namespace App\Console\Commands\Import;

use App\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class ImportComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:comments {--file=} {--appid=}';

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
        $file  = $this->option('file');
        $appid = $this->option('appid');

        // 加载excel文件
        $reader = Excel::selectSheetsByIndex(0)->load($file);

        $offset    = 0;
        $f         = $s         = $r         = 0;
        $db        = DB::connection('mysql4');
        $step_size = 1000; // 每次处理1000条记录

        $useful_comment_id_key = "useful_comment_ids:appid_{$appid}";

        while (1) {
            // 获取用户昵称
            $rand_id   = rand(1, 200000);
            $usernames = $db->table('users')->select('user_name')->where('id', '>=', $rand_id)->limit($step_size)->pluck('user_name')->toArray();

            $results     = $reader->skipRows($offset)->takeRows($step_size)->get();
            $is_continue = false;
            $i           = 0;
            foreach ($results as $row) {
                $is_continue = true; // 没有记录不会进来
                $content     = $row->内容;

                // 判断内容是否相同
                if (DB::table('comments')->where('content', $content)->first()) {
                    $f++;
                    continue;
                }

                $data = [
                    'title'    => $row->标题,
                    'content'  => $row->内容,
                    'nickname' => $usernames[$i],
                    'appid'    => $appid,
                ];
                $i++;

                try {
                    $comment_id = DB::table('comments')->insertGetId($data);

                    if ($comment_id) {
                        $s++;
                        Redis::sAdd($useful_comment_id_key, $comment_id);
                    }

                } catch (\Exception $e) {
                    $r++;
                    continue;
                }
            }
            if (!$is_continue) {
                break;
            }
            $offset += $step_size;
        }
        echo json_encode([
            'good_num' => $s,
            'fail_num' => $r,
            'repeat_num' => $f,
        ]);

        return true;
    }
}
