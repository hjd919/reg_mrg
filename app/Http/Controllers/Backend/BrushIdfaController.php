<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BackendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BrushIdfaController extends BackendController
{
    public function save(Request $request)
    {
        $app_name          = $request->input('app_name');
        $bundleId          = $request->input('bundle_id');
        $appid             = $request->input('appid');
        $callback          = $request->input('callback');
        $callback_time     = $request->input('callback_time');
        $channel           = $request->input('channel');
        $ciliu_return_num  = $request->input('ciliu_return_num');
        $active_start_time = $request->input('active_start_time');
        $active_end_time   = $request->input('active_end_time');
        $ciliu_start_time  = $request->input('ciliu_start_time');
        $ciliu_end_time    = $request->input('ciliu_end_time');
        $is_ciliu          = $request->input('is_ciliu');
        // $mobile_group_id  = $request->input('mobile_group_id');
        $needClean         = $request->input('needClean');
        $open_time         = $request->input('open_time');
        $order_num         = $request->input('order_num');
        $apiType           = $request->input('apiType');
        $process           = $request->input('process');
        $query             = $request->input('query');
        $taskType          = $request->input('taskType');
        $active_mobile_num = $request->input('active_mobile_num');
        $ciliu_mobile_num  = $request->input('ciliu_mobile_num');

        // 判断手机在设定时间内是否有空
        // 激活任务
        $active_used_mobile_ids = $this->hasEnoughMobileNum('active', $active_mobile_num, $active_start_time, $active_end_time);
        if ($active_used_mobile_ids === false) {
            return $this->fail_response(['message' => "激活任务不够手机了，所需手机:{$active_mobile_num}"]);
        }
        // 如果是次留任务，判断次留手机是否足够
        if ($is_ciliu) {
            $ciliu_used_mobile_ids = $this->hasEnoughMobileNum('ciliu', $ciliu_mobile_num, $ciliu_start_time, $ciliu_end_time);
            if ($ciliu_used_mobile_ids === false) {
                return $this->fail_response(['message' => "次留任务不够手机了，所需手机:{$ciliu_mobile_num}"]);
            }
        }

        // 添加激活任务
        $active_task_id = DB::table('brush_idfas_active')->insertGetId(array_merge(compact(
            'app_name',
            'bundleId',
            'appid',
            'callback',
            'callback_time',
            'channel',
            'is_ciliu',
            'mobile_group_id',
            'needClean',
            'open_time',
            'order_num',
            'apiType',
            'process',
            'query',
            'taskType'
        ), [
            'mobile_num' => $active_mobile_num,
            'start_time' => $active_start_time,
            'end_time'   => $active_end_time,
        ]));

        // 分配激活任务的手机
        $mobile_ids = DB::table('brush_active_mobiles')
            ->select('id')
            ->whereNotIn('id', $active_used_mobile_ids)
            ->limit($active_mobile_num)
            ->pluck('id');
        $maa_data = [];
        foreach ($mobile_ids as $mobile_id) {
            $maa_data[] = array_merge(compact('mobile_id', 'active_task_id'), [
                'start_time' => $active_start_time,
                'end_time'   => $active_end_time,
            ]);
        }
        DB::table('mobile_assign_active')->insert($maa_data);

        // 如果是次留任务，添加次留任务
        if ($is_ciliu) {
            // brush_idfas_ciliu
            $ciliu_task_id = DB::table('brush_idfas_ciliu')->insertGetId([
                'active_task_id'   => $active_task_id,
                'mobile_num'       => $ciliu_mobile_num,
                'ciliu_return_num' => $ciliu_return_num,
                'start_time'       => $ciliu_start_time,
                'end_time'         => $ciliu_end_time,
            ]);

            // 分配次留任务的手机 brush_ciliu_mobiles
            $mobile_ids = DB::table('brush_ciliu_mobiles')
                ->select('id')
                ->whereNotIn('id', $ciliu_used_mobile_ids)
                ->limit($ciliu_mobile_num)
                ->pluck('id');
            $mac_data = [];
            foreach ($mobile_ids as $mobile_id) {
                $mac_data[] = array_merge(compact('mobile_id', 'ciliu_task_id'), [
                    'start_time' => $ciliu_start_time,
                    'end_time'   => $ciliu_end_time,
                ]);
            }
            DB::table('mobile_assign_ciliu')->insert($mac_data);
        }

        return $this->success_response(['message' => "ok"]);
    }

    public function hasEnoughMobileNum($type, $mobile_num, $start_time, $end_time)
    {
        // 1. 查询3种不可用情况下的手机id
        $used_mobile_ids = DB::table('mobile_assign_' . $type)->where([
            ['start_time', '>=', $start_time],
            ['start_time', '<', $end_time],
        ])->orWhere([
            ['end_time', '>=', $start_time],
            ['end_time', '<', $end_time],
        ])->orWhere([
            ['start_time', '<=', $start_time],
            ['end_time', '>=', $end_time],
        ])->groupBy('mobile_id')->pluck('mobile_id');
        $used_mobile_num   = count($used_mobile_ids);
        $total_mobile_num  = DB::table('brush_' . $type . '_mobiles')->count();
        $remain_mobile_num = $total_mobile_num - $used_mobile_num;

        Log::info(json_encode(compact('used_mobile_num', 'total_mobile_num', 'remain_mobile_num', 'mobile_num')));

// 判断可用手机数是否足够
        if ($remain_mobile_num < $mobile_num) {
            return false;
        }

        // 返回已经用的手机数
        return $used_mobile_ids;
    }
}
