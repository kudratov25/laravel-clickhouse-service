this repo intendeed to the exporitng datas to the clickhouse server. 
#1.1composer install 
```
adjust .env file
CLICKHOUSE_HOST=
CLICKHOUSE_PORT=8443
CLICKHOUSE_DATABASE=default
CLICKHOUSE_USERNAME=default
CLICKHOUSE_PASSWORD=
CLICKHOUSE_TIMEOUT_CONNECT=2
CLICKHOUSE_TIMEOUT_QUERY=2

//use if server in the local comment it
CLICKHOUSE_HTTPS=true
```
then may export data in the controllers
#1.2
code example
 ```public function exportData()
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
```
