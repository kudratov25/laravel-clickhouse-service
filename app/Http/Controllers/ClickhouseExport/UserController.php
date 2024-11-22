<?php

namespace App\Http\Controllers\ClickhouseExport;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ClickHouseExportService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function exportData()
    {
        $columns = [
            'id',
            'name',
            'email',
            'email_verified_at',
            'password',
            'remember_token',
            'created_at',
            'updated_at'
        ];

        $model = User::class;
        $tableName = 'users';

        $service = new ClickHouseExportService($model, $columns, $tableName, 1000);

        $response = $service->exportData();

        if ($response['status'] === 'success') {
            return response()->json($response);
        } else {
            return response()->json($response, 500);
        }
    }
}
