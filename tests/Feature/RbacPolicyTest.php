<?php

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('rbac authorization matrix is explicitly defined in configuration', function () {
    $matrix = config('rbac.abilities');

    expect($matrix)->toBeArray();
    expect($matrix)->toHaveKeys(['team', 'project', 'admin']);
    expect($matrix['team'])->toHaveKeys(['view', 'update', 'delete', 'manage_members']);
    expect($matrix['project'])->toHaveKeys(['view', 'create', 'update', 'delete']);
    expect($matrix['admin'])->toHaveKey('access');
});

test('team policy enforces owner, team_admin, and member matrix', function (string $role, array $expectations) {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($user->id, ['role' => $role]);

    DB::table('subscriptions')->insert([
        'user_id' => $owner->id,
        'type' => 'default',
        'stripe_id' => 'sub_'.Str::random(14),
        'stripe_status' => 'active',
        'stripe_price' => 'price_team',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($user->can('view', $team))->toBe($expectations['view']);
    expect($user->can('update', $team))->toBe($expectations['update']);
    expect($user->can('delete', $team))->toBe($expectations['delete']);
    expect($user->can('manageMembers', $team))->toBe($expectations['manageMembers']);
})->with([
    'owner' => [
        'owner',
        ['view' => true, 'update' => true, 'delete' => true, 'manageMembers' => true],
    ],
    'team_admin' => [
        'team_admin',
        ['view' => true, 'update' => true, 'delete' => false, 'manageMembers' => true],
    ],
    'member' => [
        'member',
        ['view' => true, 'update' => false, 'delete' => false, 'manageMembers' => false],
    ],
]);

test('project policy enforces owner, team_admin, and member matrix', function (string $role, array $expectations) {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($user->id, ['role' => $role]);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);

    DB::table('subscriptions')->insert([
        'user_id' => $owner->id,
        'type' => 'default',
        'stripe_id' => 'sub_'.Str::random(14),
        'stripe_status' => 'active',
        'stripe_price' => 'price_team',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($user->can('view', $project))->toBe($expectations['view']);
    expect($user->can('update', $project))->toBe($expectations['update']);
    expect($user->can('delete', $project))->toBe($expectations['delete']);
})->with([
    'owner' => [
        'owner',
        ['view' => true, 'update' => true, 'delete' => true],
    ],
    'team_admin' => [
        'team_admin',
        ['view' => true, 'update' => true, 'delete' => false],
    ],
    'member' => [
        'member',
        ['view' => true, 'update' => false, 'delete' => false],
    ],
]);

test('default deny applies to users outside the team and project scope', function () {
    $outsider = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();
    $project->teams()->attach($team->id, ['assigned_at' => now()]);

    expect($outsider->can('view', $team))->toBeFalse();
    expect($outsider->can('update', $team))->toBeFalse();
    expect($outsider->can('view', $project))->toBeFalse();
    expect($outsider->can('update', $project))->toBeFalse();
});
