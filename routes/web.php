<?php

use App\Http\Controllers\ClickHouseController;
use App\Http\Controllers\ClickhouseExport\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/clickhouse', [ClickHouseController::class, 'index']);

Route::get('/clickhouse/user', [UserController::class, 'exportData']);
