<?php

use App\Jobs\ProcessQueuedTask;
use App\Support\QueueFailureRecorder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('retry and backoff strategy is centrally defined and consumed by jobs', function () {
    config()->set('queue.worker.tries', 4);
    config()->set('queue.worker.backoff', [2, 8, 30]);

    $job = new ProcessQueuedTask;

    expect($job->tries)->toBe(4);
    expect($job->backoff())->toBe([2, 8, 30]);
});

test('dead-letter storage schema supports operational triage', function () {
    expect(Schema::hasTable('dead_letter_jobs'))->toBeTrue();
    expect(Schema::hasColumns('dead_letter_jobs', [
        'id',
        'connection',
        'queue',
        'job_uuid',
        'job_name',
        'payload',
        'exception',
        'failed_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('queue failure recorder persists dead-letter records for replay workflows', function () {
    $payload = [
        'uuid' => (string) Str::uuid(),
        'displayName' => 'App\\Jobs\\ProcessQueuedTask',
        'data' => ['attempt' => 5],
    ];

    $record = app(QueueFailureRecorder::class)->record(
        connection: 'redis',
        queue: 'default',
        payload: $payload,
        exception: 'Simulated queue failure',
    );

    $this->assertDatabaseHas('dead_letter_jobs', [
        'id' => $record->id,
        'connection' => 'redis',
        'queue' => 'default',
        'job_uuid' => $payload['uuid'],
        'job_name' => 'App\\Jobs\\ProcessQueuedTask',
    ]);
});

test('failed job recovery process remains enabled', function () {
    expect(config('queue.failed.driver'))->not->toBe('null');
    expect(config('queue.failed.table'))->toBe('failed_jobs');
});
