<?php

use App\Models\AdminOperationalAction;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;

test('admin can view and filter searchable audit logs', function () {
    $admin = User::factory()->create(['name' => 'Primary Admin']);
    $otherAdmin = User::factory()->create(['name' => 'Ops User']);
    $team = Team::factory()->create();

    $team->users()->attach($admin->id, ['role' => 'owner']);
    $team->users()->attach($otherAdmin->id, ['role' => 'team_admin']);

    $project = Project::factory()->create();

    AdminOperationalAction::query()->create([
        'actor_user_id' => $admin->id,
        'action' => 'disable_project_webhook_tokens',
        'target_type' => 'project',
        'target_id' => $project->id,
        'before_state' => ['active_token_count' => 2],
        'after_state' => ['active_token_count' => 0, 'revoked_count' => 2],
        'metadata' => ['reason' => 'Compromise suspected'],
    ]);

    AdminOperationalAction::query()->create([
        'actor_user_id' => $otherAdmin->id,
        'action' => 'retry_dead_letter_job',
        'target_type' => 'dead_letter_job',
        'target_id' => 'dlj-123',
        'before_state' => ['attempt' => 4],
        'after_state' => ['requeued' => true],
        'metadata' => ['reason' => 'Transient upstream error'],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.index'))
        ->assertOk()
        ->assertSeeText('disable_project_webhook_tokens')
        ->assertSeeText('retry_dead_letter_job');

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.index', [
            'action' => 'disable_project_webhook_tokens',
        ]))
        ->assertOk()
        ->assertViewHas('logs', function ($logs): bool {
            return $logs->count() === 1
                && $logs->first()->action === 'disable_project_webhook_tokens';
        });

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.index', [
            'search' => 'Ops User',
        ]))
        ->assertOk()
        ->assertViewHas('logs', function ($logs): bool {
            return $logs->count() === 1
                && $logs->first()->action === 'retry_dead_letter_job';
        });
});

test('non-admin users cannot access the admin audit log view', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.audit-logs.index'))
        ->assertForbidden();
});
