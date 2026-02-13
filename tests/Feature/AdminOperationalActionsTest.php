<?php

use App\Jobs\ProcessVulnerabilityIngestionRun;
use App\Models\AdminOperationalAction;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Support\WebhookTokenManager;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('admin can retry a dead-letter ingestion job and action is audited', function () {
    Bus::fake();

    $admin = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($admin->id, ['role' => 'owner']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);

    $ingestionRunId = (string) Str::uuid();
    $job = new ProcessVulnerabilityIngestionRun(
        ingestionRunId: $ingestionRunId,
        source: 'osv',
        ingestedAt: now()->toIso8601String(),
        vulnerabilities: [[
            'osv_url' => 'https://osv.dev/OSV-2026-001',
            'cvss_score' => 9.0,
            'ecosystem' => 'npm',
            'package_name' => 'demo',
            'version' => '1.0.0',
            'fixed_version' => '1.0.1',
        ]],
    );

    $failureId = (string) Str::uuid();

    DB::table('dead_letter_jobs')->insert([
        'id' => $failureId,
        'connection' => 'database',
        'queue' => 'ingestion',
        'job_uuid' => (string) Str::uuid(),
        'job_name' => ProcessVulnerabilityIngestionRun::class,
        'project_id' => $project->id,
        'ingestion_run_id' => $ingestionRunId,
        'snapshot_id' => (string) Str::uuid(),
        'source' => 'osv',
        'attempt' => 3,
        'payload' => json_encode([
            'displayName' => ProcessVulnerabilityIngestionRun::class,
            'data' => [
                'commandName' => ProcessVulnerabilityIngestionRun::class,
                'command' => serialize($job),
            ],
        ], JSON_THROW_ON_ERROR),
        'exception' => 'Simulated ingestion failure',
        'failed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->from(route('admin.ingestion-failures.index'))
        ->post(route('admin.ingestion-failures.retry', $failureId), [
            'reason' => 'The upstream dependency mirror is healthy and retry is safe.',
            'confirmation' => 'retry',
        ])
        ->assertRedirect(route('admin.ingestion-failures.index'));

    Bus::assertDispatched(ProcessVulnerabilityIngestionRun::class, function (ProcessVulnerabilityIngestionRun $dispatchedJob) use ($ingestionRunId): bool {
        return $dispatchedJob->ingestionRunId === $ingestionRunId
            && $dispatchedJob->source === 'osv'
            && count($dispatchedJob->vulnerabilities) === 1;
    });

    $this->assertDatabaseHas('admin_operational_actions', [
        'actor_user_id' => $admin->id,
        'action' => 'retry_dead_letter_job',
        'target_type' => 'dead_letter_job',
        'target_id' => $failureId,
    ]);

    $retryAudit = AdminOperationalAction::query()
        ->where('action', 'retry_dead_letter_job')
        ->latest('created_at')
        ->first();

    expect($retryAudit?->before_state)->toBeArray();
    expect($retryAudit?->after_state)->toBeArray();
    expect($retryAudit?->after_state['requeued'] ?? null)->toBeTrue();
});

test('admin can disable active webhook tokens and action is audited', function () {
    $admin = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($admin->id, ['role' => 'owner']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);

    $manager = app(WebhookTokenManager::class);
    $first = $manager->issue($project, $admin, 'Primary Token');
    $second = $manager->issue($project, $admin, 'Secondary Token');

    expect($first['token']->revoked_at)->toBeNull();
    expect($second['token']->revoked_at)->toBeNull();

    $this->actingAs($admin)
        ->from(route('admin.ingestion-failures.index'))
        ->post(route('admin.projects.webhook-tokens.disable', $project), [
            'reason' => 'Compromise suspected from leaked CI logs; revoke all active credentials.',
            'confirmation' => 'disable',
        ])
        ->assertRedirect(route('admin.ingestion-failures.index'));

    $this->assertDatabaseCount('project_webhook_tokens', 2);
    $this->assertDatabaseMissing('project_webhook_tokens', ['id' => $first['token']->id, 'revoked_at' => null]);
    $this->assertDatabaseMissing('project_webhook_tokens', ['id' => $second['token']->id, 'revoked_at' => null]);
    $this->assertDatabaseHas('admin_operational_actions', [
        'actor_user_id' => $admin->id,
        'action' => 'disable_project_webhook_tokens',
        'target_type' => 'project',
        'target_id' => $project->id,
    ]);

    $disableAudit = AdminOperationalAction::query()
        ->where('action', 'disable_project_webhook_tokens')
        ->latest('created_at')
        ->first();

    expect($disableAudit?->before_state['active_token_count'] ?? null)->toBe(2);
    expect($disableAudit?->after_state['active_token_count'] ?? null)->toBe(0);
});

test('operational actions enforce confirmation safeguards', function () {
    $admin = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($admin->id, ['role' => 'owner']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);

    $this->actingAs($admin)
        ->from(route('admin.ingestion-failures.index'))
        ->post(route('admin.projects.webhook-tokens.disable', $project), [
            'reason' => 'Compromise suspected from leaked CI logs; revoke all active credentials.',
            'confirmation' => 'nope',
        ])
        ->assertSessionHasErrors('confirmation');
});

test('non-admin users cannot execute operational actions', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.projects.webhook-tokens.disable', $project), [
            'reason' => 'Attempting unauthorized control action.',
            'confirmation' => 'disable',
        ])
        ->assertForbidden();
});
