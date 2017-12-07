<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use App\Models\Mobile;
use App\Models\Task;
use App\Models\WorkDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TaskController extends BackendController
{
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
                $table_sql = <<<EOF
CREATE TABLE `work_detail{$work_detail_table}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1进行中 2失败 3成功 4老数据',
  `work_id` int(11) NOT NULL,
  `appid` bigint(20) NOT NULL,
  `app_id` int(11) NOT NULL,
  `email` varchar(64) NOT NULL,
  `account_id` int(11) NOT NULL,
  `password` varchar(20) NOT NULL,
  `device_id` int(11) NOT NULL DEFAULT '0',
  `udid` varchar(64) NOT NULL,
  `imei` varchar(64) NOT NULL,
  `serial` varchar(64) NOT NULL,
  `bt` varchar(64) NOT NULL,
  `wifi` varchar(64) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `report_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_id_2` (`work_id`,`account_id`),
  KEY `appid_email` (`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TRIGGER `t_work_detail{$work_detail_table}_decr_num`
BEFORE INSERT ON `work_detail{$work_detail_table}`
FOR EACH ROW
update apps set brush_num=brush_num-1 where id=new.app_id;
EOF;
                DB::statement($table_sql);

            }

            $ios_app_id = DB::table('ios_apps')->insertGetId(compact(
                'appid',
                'app_name',
                'work_detail_table',
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
        $appid   = DB::table('tasks')->where('id', $task_id)->value('appid');

        // 获取可用app量
        $usable_brush_num = WorkDetail::getUsableBrushNum($appid);

        // 获取可刷设备信息数 total-已使用设备数
        $total_device_num    = DB::table('devices')->count();
        $used_device_num     = WorkDetail::countAppNum($appid);
        $usable_brush_device = $total_device_num - $used_device_num;

        return response()->json([
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
        $start_time      = $request->input('start_time');
        $end_time        = $request->input('end_time');
        $mobile_num      = $request->input('mobile_num');
        $mobile_group_id = $request->input('mobile_group_id');
        $hot             = $request->input('hot');
        $before_rank     = $request->input('before_rank');
        $remark          = $request->input('remark');

        // 判断mobile_num和mobile_group_id必须存在一个
        if (!$mobile_num && !$mobile_group_id) {
            return response()->json(['error_code' => 2, 'message' => '空闲手机数或者手机组id必须填一个']);
        }
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

        // * 判断是否多于空闲手机数
        if (!$mobile_group_id) {
            $free_mobile_num = DB::table('mobiles')->where('mobile_group_id', 0)->count(); // 获取空闲手机数
            if ($mobile_num > $free_mobile_num) {
                return response()->json(['error_code' => 1, 'message' => '已经多于空闲手机数']);
            }
        }

        // * 添加下单关键词
        $task_keyword_id = DB::table('task_keywords')->insertGetId([
            'user_id'     => $user_id,
            'task_id'     => $task_id,
            'ios_app_id'  => $ios_app->id,
            'keyword'     => $keyword,
            'success_num' => $success_num,
            'start_time'  => $start_time,
            'end_time'    => $end_time,
            'mobile_num'  => $mobile_num,
            'hot'         => $hot,
            'before_rank' => $before_rank,
            'remark'      => $remark,
        ]);

        // 判断是否已预设mobile_group_id
        if (!$mobile_group_id) {
            // * 设置手机分组

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
        }

        // * 添加app
        $is_brushing = 1;
        $app_id      = DB::table('apps')->insertGetId([
            'user_id'         => $user_id,
            'task_id'         => $task_id,
            'task_keyword_id' => $task_keyword_id,
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
        ]);
        if (!$app_id) {
            return response()->json(['error_code' => 1]);
        }

        // 更新为已完成
        $res = DB::table('tasks')->where('id', $task_id)->update(['step' => 2, 'total_num' => $task->total_num + $success_num]);
        if (!$res) {
            return response()->json(['error_code' => 2]);
        }

        // 更新task_keywords中刚添加的app_id
        DB::table('task_keywords')->where('id', $task_keyword_id)->update([
            'app_id' => $app_id,
        ]);

        // 记录appid已经用过的量
        $old_used_num = Redis::get('used_appid:' . $ios_app->appid);
        $old_used_num = (int) $old_used_num;
        Redis::set('used_appid:' . $ios_app->appid, $old_used_num + $success_num);
        Redis::expire('used_appid:' . $ios_app->appid, 3600);

        return response()->json([
            'message'  => '添加成功',
            'app_id'   => $app_id,
            'app_name' => $ios_app->app_name,
            'keyword'  => $keyword,
        ]);
    }
}
