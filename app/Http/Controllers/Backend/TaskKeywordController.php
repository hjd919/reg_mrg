<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use App\Models\TaskKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskKeywordController extends BackendController
{
    public function query(Request $request)
    {
        $current_page = $request->input('currentPage', 1);
        $page_size    = $request->input('pageSize', 10);
        $search       = $request->input('search', '');
        $task_id      = $request->input('task_id', '');
        // DB::listen(function ($query) {
        //     echo $query->sql;
        // });
        // total
        $total = TaskKeyword::when($task_id, function ($query) use ($task_id) {
            return $query->where('task_id', $task_id);
        })->count();

        // 列表
        // offset
        $offset = ($current_page - 1) * $page_size;
        $list   = TaskKeyword::with('user')
            ->with('ios_app')
            ->when($task_id, function ($query) use ($task_id) {
                return $query->where('task_id', $task_id);
            })
            ->limit($page_size)
            ->offset($offset)
            ->orderBy('id', 'desc')
            ->get();

        // 获取关联
        foreach ($list as &$row) {
            $row->user_name = $row->user->name;
            $row->app_name  = $row->ios_app->app_name;
            $row->appid     = $row->ios_app->appid;
            unset($row->ios_app);
            unset($row->user);
        }

        // 分页
        $pagination = [
            'current'  => (int) $current_page,
            'pageSize' => (int) $page_size,
            'total'    => (int) $total,
        ];

        Log::error(var_export($pagination, true));

        return response()->json(compact('pagination', 'list'));
    }

    public static function stop(Request $request)
    {
        $app_id = $request->app_id;

        // 停止任务=修改app表的结束时间
        $res = DB::table('apps')->where('id', $app_id)->update(['end_time' => date('Y-m-d H:i:s')]);

        return response()->json(['message' => $res, 'code' => 0]);
    }
}
