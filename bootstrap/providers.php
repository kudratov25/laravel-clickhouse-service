<?php

use App\Providers\AppServiceProvider;
use PhpClickHouseLaravel\ClickhouseServiceProvider;

return [
    ClickhouseServiceProvider::class,
    AppServiceProvider::class,
];
