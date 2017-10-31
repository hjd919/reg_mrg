<?php

namespace App\Http\Controllers\Jishua;

use App\Http\Controllers\Controller;
use App\Support\Util;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // 获取任务
    public function get(
        Request $request
    ) {
        Util::die_jishua(['ok']);
    }

    // 上报任务

    // 上报失败账号
}
