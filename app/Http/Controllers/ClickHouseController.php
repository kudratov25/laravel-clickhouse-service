<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClickHouseController extends Controller
{

    public function index()
    {
        try {
            $result = DB::connection('clickhouse')->select('SHOW DATABASES;');

            dd($result);
        } catch (\Exception $e) {
            Log::error('ClickHouse connection error: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to connect to ClickHouse.']);
        }
    }
}
