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
        $total = DB::table('orders')
            ->when($search, function ($query) use ($search) {
                $key = 'id';
                return $query->where($key, $search);
            })
            ->count();
        // 列表
        $list = DB::table('orders')
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
}
