<?php
namespace App\Http\Controllers\Jishua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrushIdfaController extends Controller
{
    public function get(
        Request $request
    ) {
        $idfa      = $request->input('idfa');
        $device_id = $request->input('device_id');
        if (!$idfa || !$device_id) {
            return $this->fail_response(['message' => '缺少参数cb_params']);
        }

        // 查询手机的手机组id
        $data          = ['uuid' => $device_id];
        $brush_mobiles = DB::table('brush_mobiles')->where($data)->select('mobile_group_id')->first();
        if (!$brush_mobiles) {

            // 添加手机
            DB::table('brush_mobiles')->insert($data);
            return $this->fail_response(['message' => 'new mobile']);

        } else {
            $mobile_group_id = $brush_mobiles->mobile_group_id;
            if ($mobile_group_id == 0) {
                return $this->fail_response(['message' => 'this mobile no task']);
            }
        }

        // 根据一些条件查询任务
        $now_date = date('Y-m-d H:i:s');
        $response = DB::table('brush_idfas')
            ->where([
                ['is_brushing', '=', 1],
                ['start_time', '<=', $now_date],
                ['end_time', '>=', $now_date],
                ['mobile_group_id', '=', $mobile_group_id],
            ])
            ->whereColumn([
                ['success_idfa_num', '<', 'order_num'],
            ])
            ->first();
        if (!$response) {
            return $this->fail_response(['message' => 'no brush idfa task']);
        }
        $response->ret = 0;

        // 创建任务 brush_idfa_tasks
        $brush_idfa_id = $response->id;
        $appid         = $response->appid;
        $tid           = DB::table('brush_idfa_tasks')->insertGetId([
            'idfa'          => $idfa,
            'appid'         => $appid,
            'device_id'     => $device_id,
            'brush_idfa_id' => $brush_idfa_id,
        ]);

        $response->tid = $tid;
        $response->bid = $brush_idfa_id;

        // 统计获取成功的次数
        DB::table('brush_idfas')->where('id', $brush_idfa_id)->increment('returned');

        $cb_params = json_encode(compact('idfa', 'device_id'));

        // 回调任务，拼接回调地址
        if ($response->apiType == 1) {
            $callback_url       = urlencode(url('backend/notify_success?tid=' . $tid . '&appid=' . $appid . '&bid=' . $brush_idfa_id . '&check_token=' . base64_encode($cb_params)));
            $response->callback = str_replace('{callback}', $callback_url, $response->callback);
        }

        return response()->json($response);
    }

    public function notifySuccess(
        Request $request
    ) {
        $check_token   = $request->input('check_token', '');
        $device_id     = $request->input('device_id', '');
        $brush_idfa_id = $request->input('bid', '');
        $task_id       = $request->input('tid', '');
        $appid         = $request->input('appid', '');
        $idfa          = $request->input('idfa', '');
        if ($check_token) {
            $check_token = json_decode(base64_decode($check_token));
            if ($check_token) {
                $device_id = $check_token->device_id;
                $idfa      = $check_token->idfa;
            }
        }
        if (!$task_id || !$brush_idfa_id) {
            return $this->fail_response(['message' => 'no task_id and brush_idfa_id']);
        }

        DB::table('brush_idfa_tasks')->where('id', $task_id)->update(['task_status' => 1]);

        // 统计成功激活次数
        DB::table('brush_idfas')->where('id', $brush_idfa_id)->increment('success_idfa_num');

        return $this->success_response(['success' => 1]);
    }

    public function ciliuGet(Request $request)
    {
        // 获取有次留量的任务
        $brush_idfa_id = DB::table('brush_idfas')->select('id')
            ->where([
                ['is_brushing', '=', '1'],
                ['is_ciliu', '=', '1'],
            ])
            ->whereColumn([
                ['ciliu_returned_success', '<', 'ciliu_return_num'],
            ])->value('brush_idfa_id');
        if (!$brush_idfa_id) {
            return $this->fail_response(['message' => 'ciliu task finished']);
        }

        // todo 混淆获取id
        $brush_idfa_task = DB::table('brush_idfa_tasks')
            ->where('brush_idfa_id', $brush_idfa_id)
            ->where('task_status', 1)
            ->where('created_at', '>', date('Y-m-d', strtotime('-1 days')))
            ->limit(1)
            ->first();
        if (!$brush_idfa_task) {
            return $this->fail_response(['message' => 'no more brush_idfa_task']);
        }

        DB::table('brush_idfa_tasks')->where('id', $brush_idfa_task->id)->increment('task_status');

        // 统计获取次留次数
        DB::table('brush_idfas')->where('id', $brush_idfa_task->brush_idfa_id)->increment('ciliu_returned');

        $brush_idfa = DB::table('brush_idfas')->select('bundleId', 'process')->where('id', $brush_idfa_task->brush_idfa_id)->first();

        $brush_idfa_task->bundleId = $brush_idfa->bundleId;
        $brush_idfa_task->process  = $brush_idfa->process;

        return $this->success_response((array) $brush_idfa_task);
    }

    public function ciliuReport(Request $request)
    {
        $id     = $request->input('id', '');
        $status = $request->input('status', '');

        if ($status === '' || !$id) {
            return $this->fail_response(['message' => 'que shao id canshu']);
        }

        $brush_idfa_task = DB::table('brush_idfa_tasks')->select('brush_idfa_id')->where('id', $id)->first();

        if ($status == 1) {
            // 统计上报次数
            DB::table('brush_idfas')->where('id', $brush_idfa_task->brush_idfa_id)->increment('ciliu_returned_success');
        }

        // 变更任务状态
        $res = DB::table('brush_idfa_tasks')->where('id', $id)->increment('task_status', 1, ['status' => $status]);
        if ($res) {
            return $this->success_response((array) $brush_idfa_task);
        } else {
            return $this->fail_response();
        }
    }
}
