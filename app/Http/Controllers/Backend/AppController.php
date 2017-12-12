<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\HourlAppStat;
use App\Models\WorkDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{
    public function export(Request $request)
    {
        $yesterday = date('Y-m-d', strtotime('-1 days'));

        // 导出昨天到现在的记录
        $app_rows = App::with('user')->where('create_time', '>=', $yesterday)->get();
        foreach ($app_rows as $app_row) {
            $data[] = [
                'appid'               => $app_row->appid,
                'app_name'            => $app_row->app_name,
                'keyword'             => $app_row->keyword,
                'user_name'           => $app_row->user->user_name,
                'brush_num'           => $app_row->user->brush_num,
                'brushed_num'         => $app_row->brushed_num,
                'success_num'         => $app_row->success_num,
                'success_brushed_num' => $app_row->success_brushed_num,
                'fail_brushed_num'    => $app_row->fail_brushed_num,
                'mobile_num'          => $app_row->mobile_num,
                'start_time'          => $app_row->start_time,
                'end_time'            => $app_row->end_time,
                'real_end_time'       => $app_row->real_end_time,
                'mobile_group_id'     => $app_row->mobile_group_id,
            ];
        }

        $field_name = [
            'appid',
            'app名',
            '关键词',
            '下单人',
            '剩余量',
            '总量',
            '实际总打量',
            '成功打量',
            '失败打量',
            '手机数量',
            '打量开始',
            '打量结束',
            '实际结束',
            '手机组id',
            '关键词热度',
            '在榜前',
            '在榜后',
            '在榜时间',
        ];
    }

    public function queryHourlyStat(Request $request)
    {
        $current_page = $request->input('currentPage', 1);
        $page_size    = $request->input('pageSize', 10);
        $appid        = $request->input('appid', '');
        $app_id       = $request->input('app_id', '');
        $task_id      = $request->input('task_id', '');
        $keyword      = $request->input('keyword', '');

        // 查询条件
        $where = [];
        if ($appid) {
            $where['appid'] = $appid;
        }
        if ($keyword) {
            $keyword         = trim(urldecode($keyword));
            $app_id          = App::where(['keyword' => $keyword])->orderBy('id', 'desc')->value('id');
            $where['app_id'] = $app_id;
        }
        if ($app_id) {
            $where['app_id'] = $app_id;
        }
        if ($task_id) {
            $where['task_id'] = $task_id;
        }

        // * total
        $total = HourlAppStat::where($where)->count();

        // * 列表
        // offset
        $offset = ($current_page - 1) * $page_size;
        $list   = HourlAppStat::where($where)
            ->limit($page_size)
            ->offset($offset)
            ->orderBy('id', 'desc')
            ->get();

        // 获取关联
        foreach ($list as &$row) {
            $row->app_name = $row->app->app_name;
            $row->keyword  = $row->app->keyword;

            unset($row->app);
        }

        // 整理分页
        $pagination = [
            'current'  => (int) $current_page,
            'pageSize' => (int) $page_size,
            'total'    => (int) $total,
        ];

        return response()->json(compact('pagination', 'list'));
    }

    public function query(Request $request)
    {
        $current_page = $request->input('currentPage', 1);
        $page_size    = $request->input('pageSize', 10);
        $search       = $request->input('search', '');
        $task_id      = $request->input('task_id', '');

        $where = [];
        if ($task_id) {
            $where['task_id'] = $task_id;
        }

        // * total
        $total = DB::table('apps')
            ->where($where)
            ->when($search, function ($query) use ($search) {
                $key = 'id';
                return $query->where($key, $search);
            })
            ->count();

        // * 列表
        // offset
        $offset = ($current_page - 1) * $page_size;
        $list   = App::with('user')
            ->where($where)
            ->when($search, function ($query) use ($search) {
                $key = 'id';
                return $query->where($key, $search);
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

    public function queryOne(Request $request)
    {
        $appid = $request->input('appid', 0);
        if (!$appid) {
            return response()->json(['error_code' => 1]);
        }

        $ios_app = DB::table('ios_apps')->where('appid', $appid)->first();

        // 统计该app可刷的量
        $usable_brush_num = WorkDetail::getUsableBrushNum($appid);

        return response()->json(compact('ios_app', 'usable_brush_num'));
    }

    public function saveApp(Request $request)
    {
        $appid     = $request->input('appid');
        $app_name  = $request->input('app_name');
        $appuri    = $request->input('appuri');
        $bundle_id = $request->input('bundle_id');

        // 判断app是否存在,不存在则添加
        $app = DB::table('ios_apps')->where('appid', $appid)->first();
        if ($app) {
            return response()->json(['ios_app_id' => $app->id]);
        }

        $res = DB::table('ios_apps')->insertGetId(compact('appid', 'app_name', 'appuri', 'bundle_id'));
        if (!$res) {
            return response()->json(['error_code' => 1]);
        }

        return response()->json(['ios_app_id' => $res]);
    }
}
