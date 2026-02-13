<?php

use App\Events\IngestionFailureDetected;
use App\Jobs\ProcessVulnerabilityIngestionRun;
use App\Models\IngestionRun;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Support\QueueFailureRecorder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

test('queue failure recorder captures ingestion diagnostics metadata and emits alert hook event', function () {
    Event::fake([IngestionFailureDetected::class]);

    $project = Project::factory()->create();
    $run = IngestionRun::query()->create([
        'project_id' => $project->id,
        'source' => 'osv',
        'snapshot_id' => (string) Str::uuid(),
        'payload_hash' => str_repeat('a', 64),
        'ingested_at' => now(),
        'processed_at' => null,
    ]);

    $job = new ProcessVulnerabilityIngestionRun(
        ingestionRunId: $run->id,
        source: 'osv',
        ingestedAt: now()->toIso8601String(),
        vulnerabilities: [],
    );

    $payload = [
        'uuid' => (string) Str::uuid(),
        'displayName' => ProcessVulnerabilityIngestionRun::class,
        'attempts' => 4,
        'data' => [
            'commandName' => ProcessVulnerabilityIngestionRun::class,
            'command' => serialize($job),
        ],
    ];

    $record = app(QueueFailureRecorder::class)->record(
        connection: 'database',
        queue: 'ingestion',
        payload: $payload,
        exception: 'Simulated ingestion failure',
    );

    $this->assertDatabaseHas('dead_letter_jobs', [
        'id' => $record->id,
        'project_id' => $project->id,
        'ingestion_run_id' => $run->id,
        'snapshot_id' => $run->snapshot_id,
        'source' => 'osv',
        'attempt' => 4,
    ]);

    Event::assertDispatched(IngestionFailureDetected::class, function (IngestionFailureDetected $event) use ($record): bool {
        return $event->deadLetterJob->id === $record->id;
    });
});

test('admin ingestion diagnostics page supports project/source filtering', function () {
    $admin = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($admin->id, ['role' => 'owner']);

    $projectA = Project::factory()->create(['name' => 'Project A']);
    $projectB = Project::factory()->create(['name' => 'Project B']);

    \App\Models\DeadLetterJob::query()->create([
        'connection' => 'database',
        'queue' => 'ingestion',
        'job_uuid' => (string) Str::uuid(),
        'job_name' => ProcessVulnerabilityIngestionRun::class,
        'project_id' => $projectA->id,
        'ingestion_run_id' => (string) Str::uuid(),
        'snapshot_id' => (string) Str::uuid(),
        'source' => 'osv',
        'attempt' => 3,
        'payload' => ['demo' => true],
        'exception' => 'Failure A',
        'failed_at' => now(),
    ]);

    \App\Models\DeadLetterJob::query()->create([
        'connection' => 'database',
        'queue' => 'ingestion',
        'job_uuid' => (string) Str::uuid(),
        'job_name' => ProcessVulnerabilityIngestionRun::class,
        'project_id' => $projectB->id,
        'ingestion_run_id' => (string) Str::uuid(),
        'snapshot_id' => (string) Str::uuid(),
        'source' => 'ghsa',
        'attempt' => 2,
        'payload' => ['demo' => true],
        'exception' => 'Failure B',
        'failed_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.ingestion-failures.index', [
        'project_id' => $projectA->id,
        'source' => 'osv',
    ]));

    $response->assertOk();
    $response->assertSeeText('Project A');
    $response->assertDontSeeText('Project B');
});

test('non-admin users cannot access ingestion diagnostics page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.ingestion-failures.index'))
        ->assertForbidden();
});
