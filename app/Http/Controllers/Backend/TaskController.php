<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use App\Models\Mobile;
use App\Models\Task;
use App\Models\WorkDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends BackendController
{
    public function query(Request $request)
    {
        $current_page = $request->input('currentPage', 1);
        $page_size    = $request->input('pageSize', 10);
        $search       = $request->input('search', '');

        // total
        $total = DB::table('tasks')
            ->when($search, function ($query) use ($search) {
                $key = 'id';
                return $query->where($key, $search);
            })
            ->count();
        // 列表
        $list = DB::table('tasks')
            ->when($search, function ($query) use ($search) {
                $key = 'id';
                return $query->where($key, $search);
            })
            ->limit($page_size)
            ->orderBy('id', 'desc')
            ->get();
        // 分页
        $pagination = [
            'current'  => $current_page,
            'pageSize' => $page_size,
            'total'    => $total,
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
            $ios_app_id = DB::table('ios_apps')->insertGetId(compact('appid', 'app_name', 'bundle_id'));
            if (!$ios_app_id) {
                return response()->json(['error_code' => 2]);
            }
        } else {
            $ios_app_id = $app->id;
        }

        // * 查询未完成添加的下单
        $task = DB::table('tasks')
            ->select('id')
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

        $task_id = $request->task_id;
        $appid   = DB::table('tasks')->where('id', $task_id)->value('appid');

        // 获取可用app量
        $usable_brush_num = WorkDetail::getUsableBrushNum($appid);

        return response()->json(['free_mobile_num' => $free_mobile_num, 'usable_brush_num' => $usable_brush_num]);
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

        return response()->json([
            'message'  => '添加成功',
            'app_id'   => $app_id,
            'app_name' => $ios_app->app_name,
            'keyword'  => $keyword,
        ]);
    }
}
