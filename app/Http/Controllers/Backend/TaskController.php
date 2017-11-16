<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
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
        $appid = $request->input('appid');
        $app_name = $request->input('app_name');
        $appuri = $request->input('appuri');
        $bundle_id = $request->input('bundle_id');

        // * 添加app
        // 判断app是否存在,不存在则添加
        $app = DB::table('ios_apps')->where('appid', $appid)->first();
        if ($app) {
            return response()->json(['ios_app_id' => $app->id]);
        }

        $ios_app_id = DB::table('ios_apps')->insertGetId(compact('appid', 'app_name', 'appuri', 'bundle_id'));
        if (!$ios_app_id) {
            return response()->json(['error_code' => 1]);
        }

        // * 添加下单
        $task_id = DB::table('tasks')->insertGetId(compact(
            'ios_app_id',
            'appid',
            'app_name'
        ));
        if (!$task_id) {
            return response()->json(['error_code' => 2]);
        }
        
        return response()->json(['task_id' => $task_id]);
    }

    // 获取空闲手机数
    public function getFreeMobileNum()
    {
        $free_mobile_num = DB::table('mobiles')->where('mobile_group_id', 0)->count();

        return response()->json(['free_mobile_num' => $free_mobile_num]);
    }
}
