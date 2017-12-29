<?php
namespace App\Http\Controllers\ADM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use \Exception;

class IdfaController extends Controller
{
    const CACHE_KEY         = 'idfas';
    const CACHE_KEY_FETCHED = 'idfas_fetched';
    const CACHE_CHANNEL     = 'idfas_channel';
    const CACHE_APPID       = 'idfas_appid';
    private $appid          = 0;

    public function __construct(
        Request $request
    ) {
        $appid       = $request->input('appid');
        $this->appid = $appid ?: Redis::get(self::CACHE_APPID);
    }

    // 查询是否存在idfa
    public function isExist(
        Request $request
    ) {
        $idfa = $request->input('idfa');

        // $idfas = $request->input('idfas');
        // $idfas = explode('|', $idfas);
        //         foreach ($idfas as $idfa) {
        //     $res = $is_exist_idfa($idfa);
        // }

        if (!$idfa || strlen($idfa) < 30) {
            return response()->json(['error_code' => 1, 'message' => '缺少参数']);
        }

        try {
            $res = $this->is_exist_idfa($idfa);

            if (!$res && !Redis::sIsMember(self::CACHE_KEY_FETCHED, $idfa)) {

                // 判断不存在 且防址多次请求排重接口
                Redis::sAdd(self::CACHE_KEY_FETCHED, $idfa);

                // 记录已获取
                $db = DB::connection('mysql3');

                $channel = $request->input('channel');
                $channel = $channel ?: Redis::get(self::CACHE_CHANNEL);

                $appid = $this->appid;
                $db->table('idfas_active')->insert(['idfa' => $idfa, 'channel' => $channel, 'appid' => $appid]);
            }
        } catch (\Exception $e) {
            $res = true;
        }

        return response()->json(['error_code' => 0, $idfa => $res]);
    }

    private function is_exist_idfa($idfa)
    {
        return Redis::sIsMember($this->cache_idfa_key(), $idfa);
    }

    private function cache_idfa_key()
    {
        if ($this->appid == '1279322671') {
            return self::CACHE_KEY;
        } else {
            return self::CACHE_KEY . ':appid_' . $this->appid;
        }
    }

    public function import(
        Request $request
    ) {
        $appid = $this->appid;

        // 导入
        $rows      = file('/Users/jdhu/Downloads/ai.txt');
        $db        = DB::connection('mysql3');
        $r         = 0;
        $cache_key = $this->cache_idfa_key();
        echo "appid_{$appid}:cache_key_{$cache_key}:cache_size_" . Redis::sCard($cache_key) . "\n";

        foreach ($rows as $key => $row) {
            $idfa = trim($row);
            if (!$idfa || strlen($idfa) < 30) {
                continue;
            }
            // $res = $db->table('idfas')->where(['appid' => $appid, 'idfa' => $idfa])->first();
            // if ($res) {
            //     $r++;
            //     continue;
            // }
            // $res  = $db->table('idfas')->insert(['idfa' => $idfa, 'appid' => $appid]);
            $res1 = Redis::sAdd($cache_key, $idfa);
            // if ($res && $res1) {
                echo $key . "\n";
            // }
        }

        echo "appid_{$appid}:cache_key_{$cache_key}:cache_size_" . Redis::sCard($cache_key);
    }

    public function set_info(
        Request $request
    ) {
        $channel = $request->input('channel');
        $appid   = $request->input('appid');
        $res     = Redis::set(self::CACHE_CHANNEL, $channel);
        $res     = Redis::set(self::CACHE_APPID, $appid);
    }

    public function active(
        Request $request
    ) {
        $idfa = $request->input('idfa');
        if (!$idfa || strlen($idfa) < 30) {
            return response()->json(['error_code' => 1, 'message' => '缺少参数']);
        }

        try {
            $res = $this->is_exist_idfa($idfa);
            if ($res) {
                // 已存在
                return response()->json(['error_code' => 2, 'message' => 'active fail']);
            }
            $db = DB::connection('mysql3');

            DB::beginTransaction();

            // 判断是否作弊 判断从获取到激活时间
            $idfas_active = $db->table('idfas_active')->where(['idfa' => $idfa])->first();
            if (!$idfas_active) {
                return response()->json(['error_code' => 4, 'message' => 'active fail']);
            }
            // $fetched_time = strtotime($idfas_active->created_at);
            // if(time() - $fetched_time < 60){
            //     return response()->json(['error_code' => 5, 'message' => 'active fail']);
            // }

            $appid = $this->appid;
            // 记录激活 和 总idfa
            $res = $db->table('idfas')->insert(['idfa' => $idfa, 'appid' => $appid]);
            if (!$res) {
                throw new Exception('db error');
            }
            $res = $db->table('idfas_active')->where(['idfa' => $idfa, 'appid' => $appid])->update(['is_active' => 1]);
            if (!$res) {
                DB::rollBack();
            }

            // 添加到idfa缓存
            $res = Redis::sAdd($this->cache_idfa_key(), $idfa);
            if (!$res) {
                DB::rollBack();
            }

            DB::commit();
        } catch (Exception $e) {
            return response()->json(['error_code' => 3, 'message' => 'server error' . $e->getMessage()]);
        }

        return response()->json(['error_code' => 0, 'message' => 'success']);
    }

    // public function save_cache()
    // {
    //     $db = DB::connection('mysql3');

    //     $rows = $db->table('idfas')->get();
    //     $key  = self::CACHE_KEY;
    //     $f    = $s    = 0;
    //     foreach ($rows as $row) {
    //         $res = Redis::sAdd($key, $row->idfa);
    //         if ($res) {
    //             $s++;
    //         } else {
    //             $f++;
    //         }
    //     }
    //     echo json_encode(compact('s', 'j'));
    // }
}
