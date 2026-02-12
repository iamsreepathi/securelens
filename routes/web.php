<?php

use App\Support\HealthChecks;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/health', function (HealthChecks $healthChecks) {
    $report = $healthChecks->report();
    $statusCode = $report['status'] === 'ok' ? 200 : 503;

    return response()->json($report, $statusCode);
})->name('health');

require __DIR__.'/settings.php';
