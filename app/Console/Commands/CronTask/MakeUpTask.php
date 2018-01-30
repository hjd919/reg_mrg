<?php

namespace App\Console\Commands\CronTask;

use App\Models\App;
use App\Models\Mobile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeUpTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make_up:task';

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
        // * 判断是否有空闲手机
        $mobiles     = DB::table('mobiles')->where(['mobile_group_id' => 0, 'is_normal' => 1])->get()->toArray();
        $spare_count = count($mobiles);
        if (!$spare_count) {
            echo 'no mobile spare--' . $spare_count;
            return true;
        }

        do {
            DB::beginTransaction();
            $spare_app = DB::table('spare_apps')
                ->orderBy('status', 'desc')
                ->orderBy('order_type', 'desc')
                ->orderBy('id', 'asc')
                ->whereIn('status', [0, 1])
                ->first();
            if (!$spare_app) {
                // 没有任务
                break;
            }

            // 1.已经在跑的
            $add_task = function (
                $used_num,
                $remain_mobile_num, // spare_app
                $is_enough = 0,
                $spare_app
            ) {
                $app_row = App::find($spare_app->app_id);

                // *mobile_group_id
                $mobile_group_id = Mobile::setMobileGroupId($used_num, $app_row->mobile_group_id);

                // update app
                App::where('id', $spare_app->app_id)->increment('mobile_num', $used_num);

                // spare_apps
                DB::table('spare_apps')->where('id', $spare_app->id)
                    ->update([
                        'status'            => $is_enough ? 2 : 1,
                        'remain_mobile_num' => $remain_mobile_num,
                    ]);
            };

            // 2.没跑的
            $add_task2 = function (
                $used_num,
                $remain_mobile_num, // spare_app
                $is_enough = 0,
                $spare_app
            ) {
                $end_time = date('Y-m-d H:i:s', $spare_app->brush_hour * 3600 + time());

                // *mobile_group_id
                $mobile_group_id = Mobile::setMobileGroupId($used_num);
                echo "mobile_group_id:{$mobile_group_id}\n";

                // * 添加app
                $app_data = [
                    'user_id'         => 0,
                    'task_id'         => $spare_app->task_id,
                    'ios_app_id'      => $spare_app->ios_app_id,
                    'keyword'         => $spare_app->keyword,
                    'brush_num'       => $spare_app->success_num,
                    'success_num'     => $spare_app->success_num,
                    'start_time'      => date("Y-m-d H:i:s"),
                    'end_time'        => $end_time,
                    'mobile_num'      => $used_num,
                    'appid'           => $spare_app->appid,
                    'app_name'        => $spare_app->app_name,
                    'bundle_id'       => $spare_app->bundle_id,
                    'is_brushing'     => 1,
                    'mobile_group_id' => $mobile_group_id,
                    'hot'             => $spare_app->hot,
                    'before_rank'     => $spare_app->before_rank,
                    'order_type'      => $spare_app->order_type,
                    'is_comment'      => 0,
                ];
                // echo "app_data:" . var_export($app_data, true) . "\n";
                $app_id = App::insertGetId($app_data);

                // echo "app_id:{$app_id}\n";

                // spare_apps
                $spare_apps_data = [
                    'app_id'            => $app_id,
                    'status'            => $is_enough ? 2 : 1,
                    'remain_mobile_num' => $remain_mobile_num,
                ];
                // echo "spare_apps_data:" . var_export($spare_apps_data, true) . "\n";
                $res = DB::table('spare_apps')->where([
                    'id' => $spare_app->id,
                ])->update($spare_apps_data);
                // echo "res:" . var_export($res, true) . "\n";
            };

            // * 给空闲手机分配任务
            if ($spare_count >= $spare_app->remain_mobile_num) { // 多了，继续下一个任务获取
                $spare_count -= $spare_app->remain_mobile_num;
                $used_num          = $spare_app->remain_mobile_num;
                $remain_mobile_num = 0;

                if ($spare_app->status == 1) {
                    // 1.在跑的
                    $add_task(
                        $used_num,
                        $remain_mobile_num, // spare_app
                        $is_enough = 1,
                        $spare_app
                    );

                } else {
                    // 2.没跑的
                    $add_task2(
                        $used_num,
                        $remain_mobile_num, // spare_app
                        $is_enough = 1,
                        $spare_app
                    );
                }

            } else { // 少了不够用
                $used_num          = $spare_count;
                $remain_mobile_num = $spare_app->remain_mobile_num - $spare_count;
                $spare_count       = 0;

                if ($spare_app->status == 1) {
                    // 1.在跑的
                    $add_task(
                        $used_num,
                        $remain_mobile_num, // spare_app
                        $is_enough = 0,
                        $spare_app
                    );
                } else {
                    // 2.没跑的
                    $add_task2(
                        $used_num,
                        $remain_mobile_num, // spare_app
                        $is_enough = 0,
                        $spare_app
                    );
                }

            }
            DB::commit();
            // echo $spare_count;
        } while ($spare_count);
    }
}
