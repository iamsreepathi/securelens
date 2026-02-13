<?php

use App\Http\Controllers\Ingestion\VulnerabilityIngestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('ingestion/vulnerabilities', [VulnerabilityIngestionController::class, 'store'])
        ->name('api.ingestion.vulnerabilities.store');
});
