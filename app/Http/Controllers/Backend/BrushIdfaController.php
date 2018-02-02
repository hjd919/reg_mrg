<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrushIdfaController extends BackendController
{
    public function save(Request $request)
    {
        $app_name         = $request->input('app_name');
        $bundleId         = $request->input('bundle_id');
        $appid            = $request->input('appid');
        $callback         = $request->input('callback');
        $callback_time    = $request->input('callback_time');
        $channel          = $request->input('channel');
        $ciliu_return_num = $request->input('ciliu_return_num');
        $start_time       = $request->input('start_time');
        $end_time         = $request->input('end_time');
        $is_ciliu         = $request->input('is_ciliu');
        $mobile_group_id  = $request->input('mobile_group_id');
        $needClean        = $request->input('needClean');
        $open_time        = $request->input('open_time');
        $order_num        = $request->input('order_num');
        $apiType          = $request->input('apiType');
        $process          = $request->input('process');
        $query            = $request->input('query');
        $taskType         = $request->input('taskType');

        // 分配手机
        $mobile_num = DB::table('brush_mobiles')->where('mobile_group_id', $mobile_group_id)->count();
        // if (!$mobile_num) {
        //     return $this->fail_response(['message' => "手机组id{$mobile_group_id}没有手机可用"]);
        // }
        $mobile_num = 10;

        DB::table('brush_idfas')->insert(compact(
            'app_name',
            'bundleId',
            'appid',
            'callback',
            'callback_time',
            'channel',
            'ciliu_return_num',
            'start_time',
            'end_time',
            'is_ciliu',
            'mobile_group_id',
            'needClean',
            'open_time',
            'order_num',
            'apiType',
            'process',
            'query',
            'mobile_num',
            'taskType'
        ));

        return $this->success_response(['message' => "ok"]);
    }
}
