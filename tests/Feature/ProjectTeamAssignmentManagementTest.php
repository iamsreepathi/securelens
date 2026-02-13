<?php

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

function grantAssignmentSubscription(User $owner): void
{
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
}

test('authorized admins can assign teams and visibility updates immediately', function () {
    $ownerA = User::factory()->create();
    $admin = User::factory()->create();
    $ownerB = User::factory()->create();
    $memberB = User::factory()->create();

    $teamA = Team::factory()->create(['name' => 'Team A']);
    $teamB = Team::factory()->create(['name' => 'Team B']);
    $project = Project::factory()->create(['name' => 'Scanner Core']);

    $teamA->users()->attach($ownerA->id, ['role' => 'owner']);
    $teamA->users()->attach($admin->id, ['role' => 'team_admin']);
    $teamB->users()->attach($ownerB->id, ['role' => 'owner']);
    $teamB->users()->attach($admin->id, ['role' => 'team_admin']);
    $teamB->users()->attach($memberB->id, ['role' => 'member']);
    $project->teams()->attach($teamA->id, ['assigned_at' => now()]);

    grantAssignmentSubscription($ownerA);
    grantAssignmentSubscription($ownerB);

    Log::spy();

    $this->actingAs($admin)
        ->post(route('projects.teams.store', $project), [
            'team_id' => $teamB->id,
        ])
        ->assertRedirect(route('projects.show', $project));

    expect($project->teams()->whereKey($teamB->id)->exists())->toBeTrue();

    $this->actingAs($memberB)
        ->get(route('projects.show', $project))
        ->assertOk();

    Log::shouldHaveReceived('info')
        ->once()
        ->with('project.assignment.changed', \Mockery::on(function (array $context) use ($project, $teamB): bool {
            return $context['action'] === 'assigned'
                && $context['project_id'] === $project->id
                && $context['team_id'] === $teamB->id;
        }));
});

test('unauthorized users cannot change project assignments', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create();
    $team = Team::factory()->create();
    $otherTeam = Team::factory()->create();

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($member->id, ['role' => 'member']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);

    $this->actingAs($member)
        ->post(route('projects.teams.store', $project), ['team_id' => $otherTeam->id])
        ->assertForbidden();

    $this->actingAs($member)
        ->delete(route('projects.teams.destroy', [$project, $team]))
        ->assertForbidden();
});

test('cannot unassign the last team from a project', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);
    grantAssignmentSubscription($owner);

    $this->actingAs($owner)
        ->delete(route('projects.teams.destroy', [$project, $team]))
        ->assertSessionHasErrors(['team']);

    expect($project->teams()->whereKey($team->id)->exists())->toBeTrue();
});
