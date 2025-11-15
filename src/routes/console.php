<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\ScanScheduler;

Artisan::command('scan:run', function () {
    app(ScanScheduler::class)->run();
})->describe('Run the dynamic scan scheduler');

Schedule::call(function () {
    app(ScanScheduler::class)->run();
})->hourly();