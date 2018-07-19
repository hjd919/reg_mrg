<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function exportAppleid(Request $request)
    {
        if ($request->isMethod('post')) {
            $rows = DB::table('appleids')->where('updated_at', '>', date('Y-m-d', strtotime('-1 days')))->where('state', 1)->get();
            if (!$rows) {
                return 'no rows';
            }
            $date        = date('Ymd_H');
            $filepath    = storage_path("app/{$date}.csv");
            $file_handle = fopen($filepath, "w");

            foreach ($rows as $row) {
                $csv_row = [$row->strRegName, $row->strRegPwd];
                $res     = fputcsv($file_handle, $csv_row);
                if (!$res) {
                    continue;
                }
                DB::table('appleids')->where('id', $row->id)->update(['state' => 3000]);
            }

            return response()->download($filepath);
        }
        
        $num = DB::table('appleids')->where('updated_at', '>', date('Y-m-d', strtotime('-1 days')))->where('state', 1)->count();
        return view('exportAppleid', ['num' => $num]);
    }

}
