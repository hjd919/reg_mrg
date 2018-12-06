<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CpAppleidDevice extends Command
{

    protected $signature   = 'CpAppleidDevice';
    protected $description = '';

    public function handle()
    {
        // 获取账号
	    $appleids     = DB::table('appleids')
		    ->where('created_at', '>', date('Y-m-d',strtotime('-1 days')))
		   // ->where('created_at','<',date('Y-m-d'))
		    ->where('state', 1)->get();
        $device_limit = $appleids->count();
        if (!$device_limit) {
            echo 'mei you zhang hao';
            return true;
        }
        // 获取设备
        $device_id = DB::table('configs')->where('name', 'device_id')->value('value');
        $devices   = DB::connection('prod_jishua')->table('devices14')->where('id', '>', $device_id)->limit($device_limit)->get()->toArray();
        // 整理到账号设备表
        foreach ($appleids as $key => $value) {
            $data = [
                'email'     => $value->strRegName,
                'password'  => $value->strRegPwd,
                'device_id' => $devices[$key]->id,
            ];
            $res = DB::connection('prod_jishua')->table('accounts')->insert($data);
            if (!$res) {
                echo 'shibai insert';
            } else {
                // 变成不可用
                DB::table('appleids')->where('id', $value->id)->update(['state' => 300]);
            }
        }
        $device_id = $devices[$key]->id;
        // 更新下一个device_id
        DB::table('configs')->where('name', 'device_id')->update(['value' => $device_id]);

        echo 'ok';
    }
}
