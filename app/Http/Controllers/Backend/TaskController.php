<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use App\Models\App;
use App\Models\Mobile;
use App\Models\Task;
use App\Models\WorkDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TaskController extends BackendController
{
    protected $error_message = '';

    public function query(Request $request)
    {
        $current_page = $request->input('currentPage', 1);
        $page_size    = $request->input('pageSize', 10);
        $appid        = $request->input('appid', '');

        // * total
        $total = Task::when($appid, function ($query) use ($appid) {
            return $query->where('appid', $appid);
        })
            ->count();

        // * 列表
        // offset
        $offset = ($current_page - 1) * $page_size;
        $list   = Task::with('user')
            ->when($appid, function ($query) use ($appid) {
                return $query->where('appid', $appid);
            })
            ->limit($page_size)
            ->offset($offset)
            ->orderBy('id', 'desc')
            ->get();

        // 获取关联
        foreach ($list as &$row) {
            $row->user_name = $row->user->name;
            unset($row->user);
        }

        // 整理分页
        $pagination = [
            'current'  => (int) $current_page,
            'pageSize' => (int) $page_size,
            'total'    => (int) $total,
        ];

        return response()->json(compact('pagination', 'list'));
    }

    public function save(Request $request)
    {
        $user      = $this->guard()->user();
        $user_id   = $user->id;
        $appid     = $request->input('appid');
        $app_name  = $request->input('app_name');
        $bundle_id = $request->input('bundle_id');

        // * 添加app
        // 判断app是否存在,不存在则添加
        $app = DB::table('ios_apps')->select('id')->where('appid', $appid)->first();
        if (!$app) {

            // * 如果work_detail最近的表数据量大于10000数据，则新建表
            $work_detail_table = DB::table('ios_apps')->max('work_detail_table');

            $total_rows = DB::table("work_detail{$work_detail_table}")->count();
            if ($total_rows >= 500000) {
                $work_detail_table++;
                $table_sql1 = <<<EOF
CREATE TABLE `work_detail{$work_detail_table}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1进行中 2失败 3成功 4老数据',
  `fail_reason` TINYINT(1) NOT NULL DEFAULT '0',
  `work_id` int(11) NOT NULL,
  `appid` bigint(20) NOT NULL,
  `app_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL DEFAULT '0',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `report_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_id_2` (`work_id`,`account_id`),
  KEY `appid_email` (`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;
                $table_sql2 = <<<EOF
CREATE TRIGGER `t_work_detail{$work_detail_table}_decr_num` BEFORE INSERT ON `work_detail{$work_detail_table}`
 FOR EACH ROW update apps set brush_num=brush_num-1 where id=new.app_id;
EOF;
                DB::statement($table_sql1);

            }

            // 新app时需要获取当前email最大的id
            $max_account_id = DB::table('emails')->max('id');

            $ios_app_id = DB::table('ios_apps')->insertGetId(compact(
                'appid',
                'app_name',
                'work_detail_table',
                'max_account_id',
                'bundle_id'));
            if (!$ios_app_id) {
                return response()->json(['error_code' => 2]);
            }

            // 记录appid和table缓存
            Redis::hSet('work_detail_table', $appid, $work_detail_table);

        } else {
            $ios_app_id = $app->id;
        }

        // * 查询未完成添加的下单
        $task = Task::select('id')
            ->where('step', 1)
            ->where('ios_app_id', $ios_app_id)
            ->first();

        if ($task) {
            return response()->json(['task_id' => $task->id]);
        }

        // * 添加下单
        $task_id = DB::table('tasks')->insertGetId(compact(
            'user_id',
            'ios_app_id',
            'appid',
            'app_name'
        ));
        if (!$task_id) {
            return response()->json(['error_code' => 3]);
        }

        return response()->json(['task_id' => $task_id]);
    }

    // 获取空闲手机数
    public function getFreeMobileNum(Request $request)
    {
        $free_mobile_num = Mobile::getUsableNum(); // 获取空闲手机数

        $exception_mobile_num = Mobile::getExceptionNum(); // 获取异常手机数

        $task_id = $request->task_id;
        $task    = DB::table('tasks')->where('id', $task_id)->first();
        $appid   = $task->appid;

        // 获取账号策略
        if (Redis::sIsMember('account_policy_2', $appid)) {
            $usable_brush_num = Redis::sDiffStore("useful_account_ids:appid_{$appid}", 'valid_account_ids', "used_account_ids:appid_{$appid}");
        } else {
            $usable_brush_num = WorkDetail::getUsableBrushNum($appid);
        }

        // 获取可刷设备信息数 total-已使用设备数
        $total_device_num    = DB::table('devices')->count();
        $used_device_num     = WorkDetail::countAppNum($appid);
        $usable_brush_device = $total_device_num - $used_device_num;

        return response()->json([
            'app_name'             => $task->app_name,
            'free_mobile_num'      => $free_mobile_num,
            'usable_brush_num'     => $usable_brush_num,
            'exception_mobile_num' => $exception_mobile_num,
            'usable_brush_device'  => $usable_brush_device,
        ]);
    }

    // 添加下单关键词、添加app、手机分组
    public function saveTaskKeyword(Request $request)
    {
        $user    = $this->guard()->user();
        $user_id = $user->id;

        $task_id         = $request->input('task_id');
        $keyword         = $request->input('keyword');
        $success_num     = $request->input('success_num');
        $mobile_num      = $request->input('mobile_num');
        $app_info        = $request->input('app_info');
        $start_time      = $request->input('start_time');
        $end_time        = $request->input('end_time');
        $mobile_group_id = $request->input('mobile_group_id');

        // 判断mobile_num和mobile_group_id必须存在一个
        if ($mobile_group_id && $mobile_group_id < 1000) {
            return response()->json(['error_code' => 3, 'message' => '手机组id是测试用，且需要小于1000']);
        }

        // 判断日期时间
        if (!$start_time || !$end_time
            || strtotime($start_time) >= strtotime($end_time)
            || strtotime($end_time) <= time()) {
            return response()->json(['error_code' => 3, 'message' => '开始时间应该小于结束时间，且结束时间大于当前时间']);
        }

        $task    = Task::find($task_id);
        $ios_app = $task->ios_app;

        $total_success_num = 0; // 总使用账号量

        $total_hour = floor((strtotime($end_time) - strtotime($start_time)) / 3600); // 所需小时
        $total_hour *= 26; // 所能打到的成功量
        $app_ids = [];

        $app_info = explode("\n", $app_info); //行
        foreach ($app_info as $app_info_row) {

            // 格式化app_info内容
            $app_info_row = preg_replace('#\s+#', ' ', $app_info_row);
            $app_info_row = trim($app_info_row);

            // 判断是否为空行
            if (!$app_info_row) {
                continue;
            }

            $app_info_row = explode(' ', $app_info_row);

            // 判断app_info格式是否正确
            if (!isset($app_info_row[3])) {
                $this->error_message = '输入内容不正确，空格分割且含有4个纬度的值！';
                break;
            }

            list($keyword, $before_rank, $hot, $success_num) = $app_info_row;

            // 判断关键词半小时内是否存在
            if (App::where('create_time', '>', date('Y-m-d H:i:s', strtotime('-30 minutes')))->where('is_brushing', 1)->where('appid', $ios_app->appid)->where('keyword', $keyword)->first()) {
                $this->error_message = '已经存在该app的关键词【' . $keyword . '】了，别重复添加';
                break;
            }

            // 判断app_info格式是否正确
if (empty($hot) || empty($before_rank) || empty($keyword) || empty($success_num)) {
                $this->error_message = '输入内容不正确，空格分割且含有4个纬度的值！';
                break;
            }

            $total_success_num += $success_num;

            // # 保存一个关键词

            // * 设置手机分组
            if (empty($mobile_group_id)) { // bug 因为mobile_group_id循环时已经存在了,1重置它 2判断这种情况

                // 计算手机数量 总量/30*所需小时
                $mobile_num = round($success_num / $total_hour);
                $mobile_num = $mobile_num <= 0 ? 1 : $mobile_num;

                // 判断是否多于空闲手机数
                $free_mobile_num = DB::table('mobiles')->where('mobile_group_id', 0)->count(); // 获取空闲手机数
                if ($mobile_num > $free_mobile_num) {
                    $this->error_message = '已经多于空闲手机数';
                    break;
                }

                // 获取当前分组号
                $mobile_group_id = DB::table('config')->where('keyword', 'next_mobile_group_id')->value('value');

                // 设置下一个mobile_group_id
                if ($mobile_group_id >= 999) {
                    $value = 1;
                } else {
                    $value = $mobile_group_id + 1;
                }
                DB::table('config')->where('keyword', 'next_mobile_group_id')->update(['value' => $value]);

                // 如果不存在分组，则添加分组表
                if (!DB::table('mobile_group')->find($mobile_group_id)) {
                    DB::table('mobile_group')->insert([
                        'id'     => $mobile_group_id,
                        'name'   => '随机' . $mobile_group_id,
                        'remark' => 'no remark',
                    ]);
                }

                // 更新手机分组（1000以上是自己用的)
                Mobile::updateMobileGroupId($mobile_num, $mobile_group_id);
            } else {
                $mobile_num = DB::table('mobiles')->where('mobile_group_id', $mobile_group_id)->count(); // 获取空闲手机数
            }

            // * 添加app
            $is_brushing = 1;
            $app_id      = DB::table('apps')->insertGetId([
                'user_id'         => $user_id,
                'task_id'         => $task_id,
                'ios_app_id'      => $ios_app->id,
                'keyword'         => $keyword,
                'brush_num'       => $success_num,
                'success_num'     => $success_num,
                'start_time'      => $start_time,
                'end_time'        => $end_time,
                'mobile_num'      => $mobile_num,
                'appid'           => $ios_app->appid,
                'app_name'        => $ios_app->app_name,
                'bundle_id'       => $ios_app->bundle_id,
                'is_brushing'     => $is_brushing,
                'mobile_group_id' => $mobile_group_id,
                'hot'             => $hot,
                'before_rank'     => $before_rank,
            ]);

            $app_ids[] = compact('app_id', 'keyword');

            unset($mobile_group_id);
        }

        // 抓取关键词的hot和before_rank
        // pclose(popen("php ./artisan fetch:keyword_rank --appid={$ios_app->appid} --app_ids=" . json_encode($app_ids), "r"));

        // 判断没有添加的，且有错误的
        if (!$app_ids && $this->error_message) {
            return response()->json(['error_code' => 2, 'message' => $this->error_message]);
        }

        // 更新为已完成
        $res = DB::table('tasks')->where('id', $task_id)->update(['step' => 2, 'total_num' => $task->total_num + $total_success_num]);
        if (!$res) {
            return response()->json(['error_code' => 2]);
        }

        // 记录appid已经用过的量
        $old_used_num = Redis::get('used_appid:' . $ios_app->appid);
        $old_used_num = (int) $old_used_num;
        Redis::set('used_appid:' . $ios_app->appid, $old_used_num + $total_success_num);
        Redis::expire('used_appid:' . $ios_app->appid, 3600);

        $message = $this->error_message ?: '添加成功';

        return response()->json([
            'message'  => $message,
            'app_ids'  => $app_ids,
            'app_name' => $ios_app->app_name,
        ]);
    }

    // 停止任务
    public function stop(Request $request)
    {
        $task_id = $request->input('task_id', 0);
        if (!$task_id) {
            return response()->json([
                'message'    => '缺少task_id',
                'error_code' => 1,
            ]);
        }

        $task = DB::table('tasks')->find($task_id);

        $res = App::where('task_id', $task_id)->update(['end_time' => date('Y-m-d H:i:s')]);
        if ($res) {
            return response()->json([
                'message' => "成功停止{$res}条任务数，单名为-{$task->app_name}",
            ]);
        }
    }
}
