<?php

use App\Models\DeadLetterJob;
use App\Models\IngestionRun;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('admin dashboard shows cross-tenant summaries, ingestion trends, and queue health indicators', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    User::factory()->create();

    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();

    $teamA->users()->attach($admin->id, ['role' => 'owner']);
    $teamB->users()->attach($member->id, ['role' => 'member']);

    $projectA = Project::factory()->create(['is_active' => true]);
    $projectB = Project::factory()->create(['is_active' => true]);
    $projectC = Project::factory()->create(['is_active' => false]);

    $projectA->teams()->attach($teamA->id, ['assigned_at' => now()]);
    $projectB->teams()->attach($teamB->id, ['assigned_at' => now()]);
    $projectC->teams()->attach($teamA->id, ['assigned_at' => now()]);

    $today = CarbonImmutable::now()->startOfDay();
    $threeDaysAgo = $today->subDays(3);

    IngestionRun::query()->create([
        'project_id' => $projectA->id,
        'source' => 'osv',
        'snapshot_id' => (string) Str::uuid(),
        'payload_hash' => str_repeat('a', 64),
        'ingested_at' => $today->addHours(1),
        'processed_at' => $today->addHours(2),
    ]);

    IngestionRun::query()->create([
        'project_id' => $projectB->id,
        'source' => 'osv',
        'snapshot_id' => (string) Str::uuid(),
        'payload_hash' => str_repeat('b', 64),
        'ingested_at' => $today->subDay()->addHours(1),
        'processed_at' => $today->subDay()->addHours(2),
    ]);

    IngestionRun::query()->create([
        'project_id' => $projectC->id,
        'source' => 'ghsa',
        'snapshot_id' => (string) Str::uuid(),
        'payload_hash' => str_repeat('c', 64),
        'ingested_at' => $threeDaysAgo->addHours(1),
        'processed_at' => null,
    ]);

    DeadLetterJob::query()->create([
        'connection' => 'database',
        'queue' => 'ingestion',
        'job_uuid' => (string) Str::uuid(),
        'job_name' => 'App\\Jobs\\ProcessVulnerabilityIngestionRun',
        'project_id' => $projectA->id,
        'ingestion_run_id' => (string) Str::uuid(),
        'snapshot_id' => (string) Str::uuid(),
        'source' => 'osv',
        'attempt' => 4,
        'payload' => ['trace' => 'x'],
        'exception' => 'Ingestion failed',
        'failed_at' => now()->subHours(6),
    ]);

    DeadLetterJob::query()->create([
        'connection' => 'database',
        'queue' => 'ingestion',
        'job_uuid' => (string) Str::uuid(),
        'job_name' => 'App\\Jobs\\ProcessVulnerabilityIngestionRun',
        'project_id' => $projectB->id,
        'ingestion_run_id' => (string) Str::uuid(),
        'snapshot_id' => (string) Str::uuid(),
        'source' => 'osv',
        'attempt' => 2,
        'payload' => ['trace' => 'y'],
        'exception' => 'Older ingestion failure',
        'failed_at' => now()->subDays(2),
    ]);

    DB::table('jobs')->insert([
        [
            'queue' => 'ingestion',
            'payload' => '{}',
            'attempts' => 1,
            'reserved_at' => null,
            'available_at' => now()->subMinute()->timestamp,
            'created_at' => now()->subMinutes(5)->timestamp,
        ],
        [
            'queue' => 'ingestion',
            'payload' => '{}',
            'attempts' => 3,
            'reserved_at' => null,
            'available_at' => now()->subMinute()->timestamp,
            'created_at' => now()->subMinutes(4)->timestamp,
        ],
        [
            'queue' => 'default',
            'payload' => '{}',
            'attempts' => 1,
            'reserved_at' => null,
            'available_at' => now()->addMinutes(30)->timestamp,
            'created_at' => now()->subMinutes(3)->timestamp,
        ],
    ]);

    DB::table('failed_jobs')->insert([
        [
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'ingestion',
            'payload' => '{}',
            'exception' => 'Recent queue failure',
            'failed_at' => now()->subHours(2),
        ],
        [
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'Old queue failure',
            'failed_at' => now()->subDays(3),
        ],
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertViewHas('tenantSummary', function (array $summary): bool {
        return $summary['teams_total'] === 2
            && $summary['projects_total'] === 3
            && $summary['projects_active'] === 2
            && $summary['users_total'] === 3
            && $summary['users_with_team_access'] === 2;
    });
    $response->assertViewHas('ingestionSummary', function (array $summary): bool {
        return $summary['runs_total'] === 3
            && $summary['runs_processed'] === 2
            && $summary['runs_pending'] === 1
            && $summary['dead_letter_total'] === 2
            && $summary['success_rate'] === 66.7;
    });
    $response->assertViewHas('queueHealth', function (array $health): bool {
        $queues = $health['queue_depths']
            ->mapWithKeys(fn (array $row): array => [$row['queue'] => $row['depth']])
            ->all();

        return $health['pending_jobs_total'] === 3
            && $health['ingestion_queue_depth'] === 2
            && $health['delayed_jobs_total'] === 1
            && $health['retrying_jobs_total'] === 1
            && $health['failed_jobs_last_24h'] === 1
            && $health['dead_letters_last_24h'] === 1
            && $queues === ['ingestion' => 2, 'default' => 1];
    });
    $response->assertViewHas('ingestionTrend', function (Collection $trend): bool {
        $successTotal = (int) $trend->sum('success_count');
        $failureTotal = (int) $trend->sum('failure_count');
        $rowsWithSuccess = $trend->filter(fn (array $row): bool => $row['success_count'] > 0)->count();
        $rowsWithFailures = $trend->filter(fn (array $row): bool => $row['failure_count'] > 0)->count();

        return $trend->count() === 7
            && $successTotal === 2
            && $failureTotal === 2
            && $rowsWithSuccess >= 1
            && $rowsWithFailures >= 1;
    });
});

test('admin dashboard is forbidden for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});
