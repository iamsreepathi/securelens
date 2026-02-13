<?php

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function grantProjectOwnerSubscription(User $owner): void
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

test('team admins can create projects and assign ownership to a team', function () {
    $owner = User::factory()->create();
    $teamAdmin = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($teamAdmin->id, ['role' => 'team_admin']);
    grantProjectOwnerSubscription($owner);

    $this->actingAs($teamAdmin)
        ->post(route('projects.store'), [
            'name' => 'Scanner Core',
            'description' => 'Main scanner service.',
            'team_id' => $team->id,
        ])
        ->assertRedirect();

    $project = Project::query()->where('slug', 'scanner-core')->firstOrFail();

    expect((bool) $project->is_active)->toBeTrue();
    expect($project->teams()->whereKey($team->id)->exists())->toBeTrue();
});

test('project settings can update active and inactive lifecycle states', function () {
    $owner = User::factory()->create();
    $teamAdmin = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Platform Team']);
    $project = Project::factory()->create(['name' => 'Scanner Core', 'is_active' => true]);

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($teamAdmin->id, ['role' => 'team_admin']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);
    grantProjectOwnerSubscription($owner);

    $this->actingAs($teamAdmin)
        ->put(route('projects.update', $project), [
            'name' => 'Scanner Core',
            'description' => 'Temporarily paused.',
            'is_active' => false,
        ])
        ->assertRedirect(route('projects.show', $project));

    expect((bool) $project->fresh()->is_active)->toBeFalse();

    $this->actingAs($teamAdmin)
        ->put(route('projects.update', $project), [
            'name' => 'Scanner Core',
            'description' => 'Back online.',
            'is_active' => true,
        ])
        ->assertRedirect(route('projects.show', $project));

    expect((bool) $project->fresh()->is_active)->toBeTrue();
});

test('archive control marks project inactive without deleting the record', function () {
    $owner = User::factory()->create();
    $teamAdmin = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create(['is_active' => true]);

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($teamAdmin->id, ['role' => 'team_admin']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);
    grantProjectOwnerSubscription($owner);

    $this->actingAs($teamAdmin)
        ->delete(route('projects.destroy', $project))
        ->assertRedirect(route('projects.show', $project));

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'is_active' => false,
    ]);
});

test('outsiders cannot view or change project settings', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);

    $this->actingAs($outsider)
        ->get(route('projects.show', $project))
        ->assertForbidden();

    $this->actingAs($outsider)
        ->put(route('projects.update', $project), [
            'name' => 'Blocked Update',
            'description' => null,
            'is_active' => true,
        ])
        ->assertForbidden();
});
