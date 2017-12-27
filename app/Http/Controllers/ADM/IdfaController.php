<?php
namespace App\Http\Controllers\ADM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use \Exception;

class IdfaController extends Controller
{
    const CACHE_KEY = 'idfas';
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
        } catch (\Exception $e) {
            $res = true;
        }

        return response()->json(['error_code' => 0, $idfa => $res]);
    }

    private function is_exist_idfa($idfa)
    {
        return Redis::sIsMember(self::CACHE_KEY, $idfa);
    }

    public function import()
    {
        // 导入
        $rows = file('/Users/jdhu/Downloads/ai.txt');
        $db   = DB::connection('mysql3');
        $r = 0;
        foreach ($rows as $key => $row) {
            $idfa = trim($row);
            if (!$idfa || strlen($idfa) < 30) {
                continue;
            }
            $res = $db->table('idfas')->where(['idfa' => $idfa])->first();
            if($res){
                $r++;
                continue;
            }
            $res = $db->table('idfas')->insert(['idfa' => $idfa]);
            if ($res) {
                echo $key . "\n";
            }
        }
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

            DB::beginTransaction();

            // 记录激活 和 总idfa
            $db  = DB::connection('mysql3');
            $res = $db->table('idfas')->insert(['idfa' => $idfa]);
            if (!$res) {
                throw new Exception('db error');
            }
            $res = $db->table('idfas_active')->insert(['idfa' => $idfa]);
            if(!$res){
                DB::rollBack();
            }

            // 添加到idfa缓存
            $res = Redis::sAdd(self::CACHE_KEY, $idfa);
            if(!$res){
                DB::rollBack();
            }

            DB::commit();
        } catch (Exception $e) {
            return response()->json(['error_code' => 3, 'message' => 'server error']);
        }

        return response()->json(['error_code' => 0, 'message' => 'success']);
    }

    public function save_cache()
    {
        $db = DB::connection('mysql3');

        $rows = $db->table('idfas')->get();
        $key  = self::CACHE_KEY;
        $f    = $s    = 0;
        foreach ($rows as $row) {
            $res = Redis::sAdd($key, $row->idfa);
            if ($res) {
                $s++;
            } else {
                $f++;
            }
        }
        echo json_encode(compact('s', 'j'));
    }
}