<?php
namespace App\Http\Controllers\Jishua;

use App\Http\Controllers\Controller;
use App\Support\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TaskController extends Controller
{
    const MAX_KEY = 9999999999;

    // 获取任务
    public function get(
        Request $request
    ) {
        // 输入
        $device_id = '1111';
        Util::log('get--header', $_SERVER['HTTP_USER_AGENT']);
        // func getid
        $get_last_id = function ($key, $init_value = '', $prefix = '') {
            $value = Redis::get($key);
            if (null === $value) {
                $value = $init_value ?: self::MAX_KEY;
                Redis::set($key, $value);
            }
            return $value;
        };

        // func setid
        $set_last_id = function ($key, $value, $prefix = '') {
            $value = Redis::set($key, $value);
            return $value;
        };

        // func 获取数据库
        $query_rows = function ($offset,
            $table,
            $where = null,
            $limit = 5,
            $order_field = 'id',
            $order_value = 'desc'
        ) {
            $rows = DB::table($table)->where([['id', '<', $offset]])
                ->when($where, function ($query) use ($where) {
                    return $query->where($where);
                })
                ->orderBy($order_field, $order_value)
                ->limit($limit)
                ->get();

            if ($rows->isEmpty()) {
                $rows = DB::table($table)->where([['id', '<', self::MAX_KEY]])
                    ->when($where, function ($query) use ($where) {
                        return $query->where($where);
                    })
                    ->orderBy($order_field, $order_value)
                    ->limit($limit)
                    ->get();
                if ($rows->isEmpty()) {
                    return false;
                }
            }
            return $rows;
        };

        // * 循环获取任务记录 正在刷、有数量
        $last_app_id = $get_last_id('last_app_id');
        $where       = [
            ['is_brushing', '=', 1],
            ['brush_num', '>', 0],
        ];
        $app_rows = $query_rows($last_app_id, 'apps', $where, 1);
        if (!$app_rows) {
            Util::die_jishua('没有任务记录数据了', 1);
        }
        Util::log('app_rows', $app_rows);
        $app_row = $app_rows->first();
        Util::log('app_row', $app_row);
        $set_last_id('last_app_id', $app_row->id);

        // * 循环获取苹果账号记录
        $last_email_id = $get_last_id('last_email_id');
        $where         = [
            'valid_status' => 1,
        ];
        $email_rows = $query_rows($last_email_id, 'emails', $where);
        if (!$email_rows) {
            Util::die_jishua('没有email记录数据了', 1);
        }
        $set_last_id('last_email_id', $email_rows->last()->id);

        // * 判断是否存在已经刷任务记录
        foreach ($email_rows as $key => $email_row) {
            $emails[] = $email_row->email;
        }
        $exist_work_detail = DB::table('work_detail')
            ->where('appid', $app_row->appid)
            ->whereIn('email', $emails)
            ->pluck('email')
            ->toArray();
        Util::log('exist_work_detail', $exist_work_detail);
        if ($exist_work_detail) {
            // 删除存在的emails
            $emails = array_diff($emails, $exist_work_detail);
            if (!$emails) {
                // 都删除了，即全部已经刷过了
                Util::die_jishua('全部存在已经刷任务记录', 1);
            }
        }

        // * 循环获取手机设备记录
        $last_device_id = $get_last_id('last_device_id');
        $device_rows    = $query_rows($last_device_id, 'devices');
        if (!$device_rows) {
            Util::die_jishua('没有device记录数据了', 1);
        }
        $set_last_id('last_device_id', $device_rows[count($device_rows) - 1]->id);
        Util::log('device_rows', $device_rows);

        // * 增加刷任务记录   -> 任务数量减一
        DB::beginTransaction();
        try {

            // 插入works
            $work_id = DB::table('works')->insertGetId([
                'app_id'    => $app_row->id,
                'appid'     => $app_row->appid,
                'app_name'  => $app_row->app_name,
                'bundle_id' => $app_row->bundle_id,
                'device_id' => $device_id,
                'fail_num'  => -1,
                'keyword'   => $app_row->keyword,
                'succ_num'  => -1,
            ]);

            // 插入work_detail
            $response = $work_detail = [];
            foreach ($email_rows as $key => $email_row) {
                $data = [
                    'work_id'    => $work_id,
                    'appid'      => $app_row->appid,
                    'app_id'     => $app_row->id,
                    'account_id' => $email_row->id,
                    'email'      => $email_row->email,
                    'password'   => $email_row->appleid_password,
                    'udid'       => $device_rows[$key]->udid,
                    'imei'       => $device_rows[$key]->imei,
                    'serial'     => $device_rows[$key]->serial_number,
                    'bt'         => $device_rows[$key]->lanya,
                    'wifi'       => $device_rows[$key]->mac,
                ];
                $work_detail[] = $data;

                // 构造所需格式的结果
                $data['keyword']  = $app_row->keyword;
                $data['app_name'] = $app_row->bundle_id;
                $response[]       = $data;
            }
            DB::table('work_detail')->insert($work_detail);

        } catch (Exception $e) {
            Util::errorLog('transaction error:file_' . __FILE__, $e->getMessage());

            DB::rollBack();
        }

        DB::commit();

        // * 返回所需格式的结果
        Util::die_jishua($response);
    }

    // 上报任务
    public function report(
        Request $request
    ) {
        // 输入
        $work_id    = $request->work_id;
        $account_id = $request->account_id;
        $succ_num   = $request->succ_num;
        $fail_num   = $request->fail_num;
        if (!$work_id || null === $succ_num || null === $fail_num) {
            Util::die_jishua('缺少参数' . $work_id . $succ_num . $fail_num);
        }

        // * 根据任务id和账号id更新刷任务记录状态
        DB::table('works')->where('id', $work_id)->update([
            'status'      => 1,
            'succ_num'    => $succ_num,
            'fail_num'    => $fail_num,
            'report_time' => date('Y-m-d H:i:s'),
        ]);

        Util::die_jishua('ok');
    }

    // 上报失败账号
    public function invalid_account(
        Request $request
    ) {
        // * 根据账号id标记账号无效
        $account_id = $request->account_id;
        if (!$account_id) {
            Util::die_jishua('缺少参数' . $account_id);
        }

        DB::table('apps')
            ->where('id', $account_id)
            ->update(['valid_status' => 0]);

        Util::die_jishua('ok');
    }
}