<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use App\Models\Task;
use App\Models\TaskKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskKeywordController extends BackendController
{
    public function query(Request $request)
    {
        $current_page = $request->input('currentPage', 1);
        $page_size    = $request->input('pageSize', 10);
        $search       = $request->input('search', '');
        $task_id      = $request->input('task_id', '');

        // total
        $total = TaskKeyword::when($task_id, function ($query) use ($task_id) {
            return $query->where('task_id', $task_id);
        })->count();

        // 列表
        $list = TaskKeyword::with('user')
            ->with('ios_app')
            ->when($task_id, function ($query) use ($task_id) {
                return $query->where('task_id', $task_id);
            })
            ->limit($page_size)
            ->orderBy('id', 'desc')
            ->get();

        // 获取关联
        foreach ($list as &$row) {
            $row->user_name = $row->user->name;
            $row->appid     = $row->ios_app->appid;
        }

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
        $appuri    = $request->input('appuri');
        $bundle_id = $request->input('bundle_id');

        // * 添加app
        // 判断app是否存在,不存在则添加
        $app = DB::table('ios_apps')->select('id')->where('appid', $appid)->first();
        if (!$app) {
            $ios_app_id = DB::table('ios_apps')->insertGetId(compact('appid', 'app_name', 'appuri', 'bundle_id'));
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
    public function getFreeMobileNum()
    {
        $free_mobile_num = DB::table('mobiles')->where('mobile_group_id', 0)->count(); // 获取空闲手机数

        return response()->json(['free_mobile_num' => $free_mobile_num]);
    }

    // 添加下单关键词、添加app、手机分组
    public function saveTaskKeyword(Request $request)
    {
        $user    = $this->guard()->user();
        $user_id = $user->id;

        $task_id     = $request->input('task_id');
        $keyword     = $request->input('keyword');
        $success_num = $request->input('success_num');
        $start_time  = $request->input('start_time');
        $mobile_num  = $request->input('mobile_num');
        $hot         = $request->input('hot');
        $before_rank = $request->input('before_rank');
        $remark      = $request->input('remark');

        $task    = Task::find($task_id);
        $ios_app = $task->ios_app;

        // * 判断是否多于空闲手机数
        $free_mobile_num = DB::table('mobiles')->where('mobile_group_id', 0)->count(); // 获取空闲手机数
        if ($mobile_num > $free_mobile_num) {
            return response()->json(['error_code' => 1, 'message' => '已经多于空闲手机数']);
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

        // 更新手机分组（1000以上是自己用的）
        DB::table('mobiles')->where('mobile_group_id', '<', 1000)->limit($mobile_num)->update(['mobile_group_id' => $mobile_group_id]);

        // * 添加app
        $is_brushing = 0;
        $res         = DB::table('apps')->insert([
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
        if (!$res) {
            return response()->json(['error_code' => 1]);
        }

        // 更新为已完成
        $res = DB::table('tasks')->where('id', $task_id)->update(['step' => 2, 'total_num' => $task->total_num + $success_num]);
        if (!$res) {
            return response()->json(['error_code' => 2]);
        }

        return response()->json(['message' => '添加成功']);
    }
}
