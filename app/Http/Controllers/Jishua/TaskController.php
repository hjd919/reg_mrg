<?php
namespace App\Http\Controllers\Jishua;

use App\Http\Controllers\Controller;
use App\Support\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TaskController extends Controller
{
    const MAX_KEY = 9999999999;

    public $fixed_device = [
        'udid'   => '49478d3f961958eecc8e5932e007064f5cd724ce',
        'imei'   => '355406076406099',
        'serial' => 'FFMS6LR6G5MN',
        'bt'     => 'bc:54:36:dd:74:1d',
        'wifi'   => 'bc:54:36:dd:74:05',
    ];

    // 获取任务
    public function get(
        Request $request
    ) {
        // 输入

        // func getdevice_id
        $get_device_id = function () {
            $ua        = $_SERVER['HTTP_USER_AGENT'];
            $data      = explode('device/', $ua);
            $device_id = empty($data[1]) ? '0' : $data[1];
            return $device_id;
        };

        // func getid
        $get_last_id = function ($key, $init_value = '', $prefix = '') {
            $value = Redis::get($key);
            if (null === $value) {
                $value = $init_value ?: self::MAX_KEY;
                Redis::set($key, $value);
                Redis::expire($key, 86400);
            }
            return $value;
        };

        // func setid
        $set_last_id = function ($key, $value, $prefix = '') {
            $value = Redis::set($key, $value);
            Redis::expire($key, 86400);
            return $value;
        };

        // func 获取数据库
        $query_rows = function ($offset,
            $table,
            $where = null,
            $limit = 3,
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

        $device_id = $get_device_id();
        if (!$device_id) {
            $error_message = 'ua中没有device_id {ua:' . $_SERVER['HTTP_USER_AGENT'];
            Util::errorLog($error_message);
            Util::die_jishua($error_message, 1);
        }

        // * 根据device_id获取手机组id
        // * 判断是否是新device_id，不是：则记录到数据库和缓存
        $row = DB::table('mobiles')->select('mobile_group_id')->where('device_id', $device_id)->first();
        if (!$row) {
            $mobile_group_id = 1; //默认组id
            DB::table('mobiles')->insert([
                'device_id'       => $device_id,
                'alias'           => '编号new',
                'mobile_group_id' => $mobile_group_id,
            ]);
        } else {
            $mobile_group_id = $row->mobile_group_id;
        }

        // * 循环获取任务记录 正在刷、有数量
        $last_app_id = $get_last_id('last_app_id');
        $where       = [
            ['brush_num', '>', 0],
            ['is_brushing', '=', 1],
            ['mobile_group_id', '=', $mobile_group_id],
        ];
        $app_rows = $query_rows($last_app_id, 'apps', $where, 1);
        if (!$app_rows) {
            Util::die_jishua('没有任务记录数据了', 1);
        }
        $app_row = $app_rows->first();
        $set_last_id('last_app_id', $app_row->id);

        // * 循环获取苹果账号记录
        $last_email_id = $get_last_id('last_email_id:appid_' . $app_row->appid);
        $where         = [
            'is_valid'     => 301,
            'valid_status' => 1,
        ];
        $email_rows = $query_rows($last_email_id, 'emails', $where);
        if (!$email_rows) {
            Util::die_jishua('没有email记录数据了', 1);
        }
        $set_last_id('last_email_id:appid_' . $app_row->appid, $email_rows->last()->id);

        // * 判断app是否刷过此设备信息
        foreach ($email_rows as $key => $email_row) {
            $emails[] = $email_row->email;
        }
        /*DB::listen(function ($query) {
        Util::log($query->sql,$query->bindings);
        });
         */
        $exist_work_detail = DB::table('work_detail')
            ->where('appid', $app_row->appid)
            ->whereIn('email', $emails)
            ->pluck('email')
            ->toArray();
        if ($exist_work_detail) {
            Util::die_jishua('app存在刷过此批量账号了', 1);
            // 删除存在的emails
            $emails_diff = array_diff($emails, $exist_work_detail);
            if (!$emails_diff) {
                // 都删除了，即全部已经刷过了
            }
        }

        // * 循环获取手机设备记录
        $key            = 'last_device_id:appid_' . $app_row->appid;
        $last_device_id = $get_last_id($key);
        $device_rows    = $query_rows($last_device_id, 'devices');
        if (!$device_rows) {
            Util::die_jishua('没有device记录数据了', 1);
        }
        $set_last_id($key, $device_rows[count($device_rows) - 1]->id);

        // * 根据任务，固定返回值中设备某项信息
        if ($app_row->fixed_device) {
            $fixed_device = explode('|', $app_row->fixed_device);
            foreach ($fixed_device as $fd) {
                ${$fd} = $this->fixed_device[$fd];
            }
        }

        // * 判断app是否刷过此设备信息
        foreach ($device_rows as $key => $device_row) {
            $udids[] = $device_row->udid;
        }
        $exist_work_detail = DB::table('work_detail')
            ->where('appid', $app_row->appid)
            ->whereIn('udid', $udids)
            ->pluck('udid')
            ->toArray();
        if ($exist_work_detail) {
            Util::die_jishua('app存在刷过此设备信息了', 1);
        }

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
                    'udid'       => empty($udid) ? $device_rows[$key]->udid : $udid,
                    'imei'       => empty($imei) ? $device_rows[$key]->imei : $imei,
                    'serial'     => empty($serial) ? $device_rows[$key]->serial_number : $serial,
                    'bt'         => empty($bt) ? $device_rows[$key]->lanya : $bt,
                    'wifi'       => empty($wifi) ? $device_rows[$key]->mac : $wifi,
                ];
                $work_detail[] = $data;

                // 构造所需格式的结果
                $data['keyword']  = $app_row->keyword;
                $data['app_name'] = $app_row->bundle_id;
                $data['app_id']   = (string) $app_row->appid;
                $response[]       = $data;
            }
            DB::table('work_detail')->insert($work_detail);

        } catch (Exception $e) {
            Util::errorLog('transaction error:file_' . __FILE__, $e->getMessage());

            DB::rollBack();
        }

        DB::commit();

        //Util::log('ok', $response);

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
        $status = 2;
        if ($succ_num) {
            $status = 3;
        }
        // * 根据任务id和账号id更新刷任务记录状态
        DB::table('work_detail')->where([
            'work_id'    => $work_id,
            'account_id' => $account_id,
        ])->update([
            'status'      => $status,
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

        DB::table('emails')
            ->where('id', $account_id)
            ->update(['valid_status' => 0]);

        Util::die_jishua('ok');
    }
}
