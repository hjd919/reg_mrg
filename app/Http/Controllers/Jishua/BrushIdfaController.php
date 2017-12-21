<?php
namespace App\Http\Controllers\Jishua;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BrushIdfaController extends Controller
{

    public function get(
        Request $request
    ) {
        $response            = DB::table('brush_idfas')->find(1);
        $response->ret = 0;
        return response()->json($response);
    }

}
