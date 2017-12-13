<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\HourlAppStat;
use App\Models\WorkDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AppController extends Controller
{
    public function export(Request $request)
    {
        $yesterday = date('Y-m-d', strtotime('-1 days'));

        $data = [];

        // 标题
        $data[] = [
            '任务ID',
            'appid',
            'app名',
            '关键词',
            '关键词热度',
            '下单人',
            '剩余量',
            '总量',
            '实际总打量',
            '成功打量',
            '失败打量',
            '成功率',
            '手机数量',
            '打量开始',
            '打量结束',
            '实际结束',
            '手机组id',
            '机刷前排名',
            '机刷后排名',
            '在榜时长',
            '在榜开始',
            '在榜结束',
        ];

        // 数据：导出昨天到现在的记录
        $app_rows = App::with('user')->where('is_brushing', 0)->where('create_time', '>=', $yesterday)->get();
        foreach ($app_rows as $app_row) {
            $data[] = [
                $app_row->id,
                $app_row->appid,
                $app_row->app_name,
                $app_row->keyword,
                $app_row->hot,
                $app_row->user->name,
                $app_row->user->brush_num,
                $app_row->brushed_num,
                $app_row->success_num,
                $app_row->success_brushed_num,
                $app_row->fail_brushed_num,
                intval($app_row->success_brushed_num / $app_row->brushed_num * 100) . '%',
                $app_row->mobile_num,
                $app_row->start_time,
                $app_row->end_time,
                $app_row->real_end_time,
                $app_row->mobile_group_id,
                $app_row->before_rank,
            ];
        }

        $filename = "brush_stat_{$yesterday}";
        Excel::create($filename, function ($excel) use ($data) {

            $excel->sheet('1', function ($sheet) use ($data) {

                $sheet->rows($data);

            });

        })->export('xlsx');

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
