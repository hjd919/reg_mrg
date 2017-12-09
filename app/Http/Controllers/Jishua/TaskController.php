<?php
namespace App\Http\Controllers\Jishua;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Models\WorkDetail;
use App\Support\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TaskController extends Controller
{
    const MAX_KEY      = 9999999999;
    const STOP_GET_APP = 'stop_get_app';

    public $fixed_device = [
        'udid'   => '49478d3f961958eecc8e5932e007064f5cd724ce',
        'imei'   => '355406076406099',
        'serial' => 'FFMS6LR6G5MN',
        'bt'     => 'bc:54:36:dd:74:1d',
        'wifi'   => 'bc:54:36:dd:74:05',
    ];

    // 重置手机为有效
    public function resetMobileValid()
    {
        $res = DB::table('mobiles')->update(['is_normal' => 1]);
        if ($res) {
            echo '成功重置手机为有效';
        }
    }

    // 设置appid缓存的key的id
    public function setLoopId(Request $request)
    {
        $type  = $request->type;
        $appid = $request->appid;
        $id    = $request->id;
        if (!$type || !$appid || !$id) {
            die('缺少参数-type,appid,id');
        }
        if (!in_array($type, ['email', 'device'])) {
            die('type要在email,device之间');
        }

        // 获取cache_key
        if ('email' == $type) {
            $key = 'last_email_id:appid_' . $appid;
        } elseif ('device' == $type) {
            $key = 'last_device_id:appid_' . $appid;
        }

        if ($type == 'email') {
            // 记录当前account_id状态
            // 1. 获取table
            $work_detail_table = DB::table('ios_apps')->where('appid', $appid)->value('work_detail_table');
            $table             = 'work_detail' . ($work_detail_table ? $work_detail_table : '');

            // 2. 统计table最大最小
            $max_account_id = DB::table($table)->where('appid', $appid)->max('account_id');
            $min_account_id = DB::table($table)->where('appid', $appid)->min('account_id');

            // 3. 更新到ios_app表
            DB::table('ios_apps')->where('appid', $appid)->update([
                'min_account_id' => $min_account_id,
                'max_account_id' => $max_account_id,
            ]);
        }

        // 设置cache的id
        $res = Redis::set($key, $id);
        Util::log('email_res', $res);

        Util::die_jishua($res);
    }

    // 让app跑新账号
    public function brushNewEmail($appid)
    {
        //判断是否在跑旧邮箱
        $is_new_email = Redis::get("is_new_email:appid_{$appid}");
        if ($is_new_email) {
            die('正在跑新邮箱，不可以跑旧邮箱');
        }
        // 1.1 标志在跑新邮箱
        Redis::set("is_new_email:appid_{$appid}", 1);
        // 1.2 更新新账号的max_id，min_id
        $max_account_id = WorkDetail::getMaxAccountId($appid);
        $min_account_id = WorkDetail::getMinAccountId($appid);
        DB::table('ios_apps')->where('appid', $appid)->update(compact('max_account_id', 'min_account_id'));
        // 1.3 更新last_id为最大id
        Redis::set(Email::get_last_id_key($appid), 99999999999);

        return '';
    }

    // * 开始任务
    public function start($is_die = true)
    {
        if (Redis::set(self::STOP_GET_APP, 0)) {
            if ($is_die) {
                Util::die_jishua('开始任务 ok');
            }

        } else {
            Util::die_jishua('开始任务 fail', 1);
        }
    }

    // * 结束任务
    public function stop($is_die = true)
    {
        if (Redis::set(self::STOP_GET_APP, 1)) {
            if ($is_die) {
                Util::die_jishua('结束任务 ok');
            }

        } else {
            Util::die_jishua('结束任务 fail', 1);
        }
    }

    // * 根据任务所需设备数分配手机的mobile_group_id
    public function dispatchMobile()
    {
        // * 停止任务获取
        $this->stop(false);

        // * 在当前跑的任务中获取所需设备数,
        $app_rows = DB::table('apps')->where([
            ['brush_num', '>', 0],
            ['mobile_group_id', '<', 1000],
            ['is_brushing', '=', 1],
        ])->get();

        // * 循环任务，统计出设备总数
        $mobile_total = 0;
        foreach ($app_rows as $app_row) {
            $mobile_total += $app_row->mobile_num;
        }

        // * 判断设备总数是否超过mobiles表的总数，超过则提示
        $db_mobile = DB::table('mobiles')->where('mobile_group_id', '<', 1000)->count();
        if ($mobile_total > $db_mobile) {
            Util::die_jishua('设备总数超过mobiles表的总数 fail-所设置手机数量:' . $mobile_total . ',mobiles总数:' . $db_mobile, 1);
        }

        // * 把mobiles表的mobile_group_id全部更新为0
        DB::table('mobiles')->where('mobile_group_id', '<', 1000)->update(['mobile_group_id' => 0]);

        // * 循环任务表对应设备数，把mobiles表的device更新为对应mobile_group_id
        foreach ($app_rows as $app_row) {
            $mobile_group_id = $app_row->mobile_group_id;
            $mobile_num      = $app_row->mobile_num;
            DB::table('mobiles')->where('mobile_group_id', 0)->limit($mobile_num)->update(['mobile_group_id' => $mobile_group_id]);
        }

        // * 开始任务获取
        $this->start(false);

        Util::die_jishua('分配手机成功');
    }

    // 获取任务
    public function get(
        Request $request
    ) {
        // * 停止任务获取
        $is_stop = Redis::get(self::STOP_GET_APP);
        if ('1' === $is_stop) {
            Util::die_jishua('停止任务获取', 1);
        }

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
                //Redis::expire($key, 86400);
            }
            return $value;
        };

        // func setid
        $set_last_id = function ($key, $value, $prefix = '') {
            $value = Redis::set($key, $value);
            //Redis::expire($key, 86400);
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
            $rows = DB::table($table)->where('id', '<', $offset)
                ->when($where, function ($query) use ($where) {
                    return $query->where($where);
                })
                ->orderBy($order_field, $order_value)
                ->limit($limit)
                ->get();

            if ($rows->isEmpty()) {

                $rows = DB::table($table)->where('id', '<', self::MAX_KEY)
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

        // 记录手机访问时间
        $res = Redis::hSet('mobiles_access_time', $device_id, time());

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
        $now_date    = date('Y-m-d H:i:s');
        $where       = [
            ['brush_num', '>', 0],
            ['start_time', '<=', $now_date],
            ['end_time', '>=', $now_date],
            ['is_brushing', '=', 1],
            ['mobile_group_id', '=', $mobile_group_id],
        ];
        $app_rows = $query_rows($last_app_id, 'apps', $where, 1);
        if (!$app_rows) {
            Util::die_jishua('没有任务记录数据了{mobile_group_id:' . $mobile_group_id, 1);
        }
        $app_row = $app_rows->first();
        $set_last_id('last_app_id', $app_row->id);

        $appid = $app_row->appid;

        // * 循环获取苹果账号记录
        // 1.在刷旧账号
        // 2.发现新账号 is_new_email:appid=1 id>max_id的存在
        // 3.跳转刷新账号->刷完新账号了
        // 4.刷完继续刷旧账号 is_new_email:appid=0
        $email_key     = 'last_email_id:appid_' . $appid;
        $last_email_id = $get_last_id($email_key);
        // $email_rows = $query_rows($last_email_id,'emails',$where);
        $is_new_email = Redis::get("is_new_email:appid_{$appid}"); // 判断是否在刷新账号
        if ($is_new_email) {
            // 3.刷新账号
            $max_account_id = DB::table('ios_apps')->select('max_account_id')->where('appid', $appid)->value('max_account_id');
            $email_rows     = DB::table('emails')
                ->where('id', '<', $last_email_id)
                ->where('id', '>', $max_account_id)
                ->where('valid_status', 1)
                ->orderBy('id', 'desc')
                ->limit(3)
                ->get();

            if ($email_rows->isEmpty()) {
                // 4.刷完新账号了，继续刷旧账号
                // 4.1 标志在刷旧账号
                Redis::set("is_new_email:appid_{$appid}", 0);
                // 4.2 获取原来旧账号min_account_id
                $min_account_id = DB::table('ios_apps')->where('appid', $appid)->value('min_account_id');
                // 4.3 更新新账号的max_id
                $max_account_id = WorkDetail::getMaxAccountId($appid);
                DB::table('ios_apps')->where('appid', $appid)->update(['max_account_id' => $max_account_id]);
                // 4.4 获取旧账号
                $email_rows = DB::table('emails')->where('id', '<', $min_account_id)
                    ->where('valid_status', 1)
                    ->orderBy('id', 'desc')
                    ->limit(3)
                    ->get();
                if ($email_rows->isEmpty()) {
                    $email_rows = false;
                }
            }

        } else {
            // 1.在刷旧账号
            $email_rows = DB::table('emails')
                ->where('id', '<', $last_email_id)
                ->where('valid_status', 1)
                ->orderBy('id', 'desc')
                ->limit(3)
                ->get();
            if ($email_rows->isEmpty()) {
                // 1.1 刷完旧账号了，从头开始刷，标志为新账号
                Redis::set("is_new_email:appid_{$appid}", 1);
                // 1.2 获取新账号
                $email_rows = DB::table('emails')->where('id', '<', self::MAX_KEY)
                    ->where('valid_status', 1)
                    ->orderBy('id', 'desc')
                    ->limit(3)
                    ->get();
                if ($email_rows->isEmpty()) {
                    $email_rows = false;
                }
            }

        }

        if (!$email_rows) {
            Util::die_jishua('该app没有苹果账号可用了', 1);
        }

        // * 判断app是否刷过此设备信息
        // foreach ($email_rows as $key => $email_row) {
        //     $account_ids[] = $email_row->id;
        // }
        // 判断是否app刷过此批量账号
        $exist_work_detail = WorkDetail::isAppBrushEmails($appid, $email_rows[0]->id);
        if ($exist_work_detail) {
            $set_last_id($email_key, $email_rows[0]->id - 50);

            Util::log('title', 'app存在刷过此批量账号了{appid:' . $appid . ',account_id:' . $email_rows->last()->id);
            Util::die_jishua('app存在刷过此批量账号了{appid:' . $appid . ',account_id:' . $email_rows->last()->id, 1);
        }

        // * 循环获取手机设备记录
        $device_key     = 'last_device_id:appid_' . $appid;
        $last_device_id = $get_last_id($device_key);

        if ($mobile_group_id >= 1008 && $mobile_group_id <= 1013) {
            // 假设备
            $where = [
                'is_real' => 0,
            ];
        }
        $device_rows = $query_rows($last_device_id, 'devices',$where);
        if (!$device_rows) {
            Util::die_jishua('没有device记录数据了', 1);
        }

        // * 根据任务，固定返回值中设备某项信息
        // if ($app_row->fixed_device) {
        //     $fixed_device = explode('|', $app_row->fixed_device);
        //     foreach ($fixed_device as $fd) {
        //         ${$fd} = $this->fixed_device[$fd];
        //     }
        // }

        // * 判断app是否刷过此设备信息
        // foreach ($device_rows as $key => $device_row) {
        //     $device_ids[] = $device_row->id;
        // }
        // $exist_work_detail = WorkDetail::isAppBrushDevices($appid, $device_rows[0]->id);
        // if ($exist_work_detail) {
        //     $set_last_id($device_key, $device_rows[count($device_rows) - 1]->id - 100);
        //     Util::log('tt', '此app存在刷过此设备device信息了' . json_encode(['appid' => $appid, 'last_device_id' => $device_rows[count($device_rows) - 1]->id]));
        //     Util::die_jishua('此app存在刷过此设备device信息了' . json_encode(['appid' => $appid, 'last_device_id' => $device_rows[count($device_rows) - 1]->id]), 1);
        // }

        // 判断都通过后，再切换循环id
        $set_last_id($device_key, $device_rows[count($device_rows) - 1]->id);
        $set_last_id($email_key, $email_rows->last()->id);

        // * 增加刷任务记录   -> 任务数量减一
        DB::beginTransaction();
        try {

            // 插入works
            $work_id = DB::table('works')->insertGetId([
                'app_id'    => $app_row->id,
                'appid'     => $appid,
                'device_id' => $device_id,
                'keyword'   => $app_row->keyword,
            ]);

            // 插入work_detail
            $response = $work_detail = [];
            foreach ($email_rows as $key => $email_row) {
                $data = [
                    'work_id'    => $work_id,
                    'appid'      => $appid,
                    'app_id'     => $app_row->id,
                    'account_id' => $email_row->id,
                    'device_id'  => $device_rows[$key]->id,
                ];
                $work_detail[] = $data;

                // 构造所需格式的结果
                $data1 = [
                    'email'    => $email_row->email,
                    'password' => $email_row->appleid_password,
                    'udid'     => empty($udid) ? $device_rows[$key]->udid : $udid,
                    'imei'     => empty($imei) ? $device_rows[$key]->imei : $imei,
                    'serial'   => empty($serial) ? $device_rows[$key]->serial_number : $serial,
                    'bt'       => empty($bt) ? $device_rows[$key]->lanya : $bt,
                    'wifi'     => empty($wifi) ? $device_rows[$key]->mac : $wifi,
                    'keyword'  => $app_row->keyword,
                    'app_name' => $app_row->bundle_id,
                    'app_id'   => (string) $appid,
                ];

                $response[] = array_merge($data, $data1);
            }

            // 添加work_detail记录
            WorkDetail::add($appid, $work_detail);
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
        WorkDetail::updateStatus($work_id, $account_id, $status);

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
