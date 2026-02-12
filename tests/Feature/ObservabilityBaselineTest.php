<?php

use App\Jobs\ProcessQueuedTask;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

test('correlation id middleware preserves inbound correlation ids', function () {
    Route::get('/__test/correlation-id', function () {
        return response()->json([
            'correlation_id' => Context::get('correlation_id'),
        ]);
    });

    $response = $this->withHeader('X-Correlation-Id', 'corr-inbound-123')
        ->get('/__test/correlation-id');

    $response->assertOk();
    $response->assertHeader('X-Correlation-Id', 'corr-inbound-123');
    $response->assertJson(['correlation_id' => 'corr-inbound-123']);
});

test('correlation id middleware generates correlation id when absent', function () {
    Route::get('/__test/correlation-id-generated', function () {
        return response()->json([
            'correlation_id' => Context::get('correlation_id'),
        ]);
    });

    $response = $this->get('/__test/correlation-id-generated');

    $response->assertOk();
    expect($response->headers->get('X-Correlation-Id'))->not->toBeEmpty();
    $response->assertJsonPath('correlation_id', $response->headers->get('X-Correlation-Id'));
});

test('health endpoint reports app db and queue checks', function () {
    $response = $this->get('/health');

    $response->assertOk();
    $response->assertJsonStructure([
        'status',
        'checks' => [
            'app' => ['status'],
            'db' => ['status', 'error'],
            'queue' => ['status', 'connection', 'failed_driver', 'dead_letter_table_exists'],
        ],
    ]);
});

test('queued task logs ingestion lifecycle with correlation id context', function () {
    Context::add('correlation_id', 'corr-job-456');

    Log::shouldReceive('channel')->twice()->with('ingestion')->andReturnSelf();
    Log::shouldReceive('info')->twice()->withArgs(function (string $message, array $context): bool {
        return $message === 'ingestion.lifecycle.process_queued_task'
            && in_array($context['stage'], ['started', 'completed'], true)
            && $context['correlation_id'] === 'corr-job-456';
    });

    (new ProcessQueuedTask)->handle();
});
