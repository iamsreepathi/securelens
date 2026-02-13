<?php

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function seedSubscriptionForUser(User $user): void
{
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
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
}

test('team and project route access matrix is enforced by role', function (string $role, array $expectations) {
    $owner = User::factory()->create();
    $user = $role === 'owner' ? $owner : User::factory()->create();
    $invitedMember = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($owner->id, ['role' => 'owner']);

    if ($role !== 'owner') {
        $team->users()->attach($user->id, ['role' => $role]);
    }

    $project->teams()->attach($team->id, ['assigned_at' => now()]);
    seedSubscriptionForUser($owner);

    $this->actingAs($user)
        ->get(route('teams.show', $team))
        ->assertStatus($expectations['team_view_status']);

    $this->actingAs($user)
        ->get(route('teams.edit', $team))
        ->assertStatus($expectations['team_update_status']);

    $this->actingAs($user)
        ->post(route('teams.members.store', $team), [
            'email' => $invitedMember->email,
            'role' => 'member',
        ])
        ->assertStatus($expectations['manage_members_status']);

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertStatus($expectations['project_view_status']);

    $this->actingAs($user)
        ->get(route('projects.edit', $project))
        ->assertStatus($expectations['project_update_status']);
})->with([
    'owner' => [
        'owner',
        [
            'team_view_status' => 200,
            'team_update_status' => 200,
            'manage_members_status' => 302,
            'project_view_status' => 200,
            'project_update_status' => 200,
        ],
    ],
    'team_admin' => [
        'team_admin',
        [
            'team_view_status' => 200,
            'team_update_status' => 200,
            'manage_members_status' => 302,
            'project_view_status' => 200,
            'project_update_status' => 200,
        ],
    ],
    'member' => [
        'member',
        [
            'team_view_status' => 200,
            'team_update_status' => 403,
            'manage_members_status' => 403,
            'project_view_status' => 200,
            'project_update_status' => 403,
        ],
    ],
]);

test('team isolation blocks cross-team access and mutation paths', function () {
    $ownerA = User::factory()->create();
    $ownerB = User::factory()->create();
    $outsider = User::factory()->create();
    $teamA = Team::factory()->create(['name' => 'Team A']);
    $teamB = Team::factory()->create(['name' => 'Team B']);
    $projectA = Project::factory()->create(['name' => 'Project A']);

    $teamA->users()->attach($ownerA->id, ['role' => 'owner']);
    $teamB->users()->attach($ownerB->id, ['role' => 'owner']);
    $teamB->users()->attach($outsider->id, ['role' => 'team_admin']);
    $projectA->teams()->attach($teamA->id, ['assigned_at' => now()]);

    seedSubscriptionForUser($ownerA);
    seedSubscriptionForUser($ownerB);

    $this->actingAs($outsider)
        ->get(route('teams.show', $teamA))
        ->assertForbidden();

    $this->actingAs($outsider)
        ->get(route('projects.show', $projectA))
        ->assertForbidden();

    $this->actingAs($outsider)
        ->put(route('projects.update', $projectA), [
            'name' => 'Unauthorized',
            'description' => null,
            'is_active' => true,
        ])
        ->assertForbidden();

    $this->actingAs($outsider)
        ->post(route('projects.teams.store', $projectA), [
            'team_id' => $teamB->id,
        ])
        ->assertForbidden();

    $this->actingAs($outsider)
        ->post(route('teams.members.store', $teamA), [
            'email' => User::factory()->create()->email,
            'role' => 'member',
        ])
        ->assertForbidden();
});
