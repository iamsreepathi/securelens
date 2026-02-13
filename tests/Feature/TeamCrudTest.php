<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function grantActiveSubscription(User $user): void
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

test('index shows only teams for the authenticated user', function () {
    $user = User::factory()->create();
    $teamA = Team::factory()->create(['name' => 'Alpha Team', 'slug' => 'alpha-team']);
    $teamB = Team::factory()->create(['name' => 'Beta Team', 'slug' => 'beta-team']);
    $outsiderTeam = Team::factory()->create(['name' => 'Outside Team', 'slug' => 'outside-team']);

    $teamA->users()->attach($user->id, ['role' => 'owner']);
    $teamB->users()->attach($user->id, ['role' => 'member']);

    $this->actingAs($user)
        ->get(route('teams.index'))
        ->assertOk()
        ->assertSeeText('Alpha Team')
        ->assertSeeText('Beta Team')
        ->assertDontSeeText('Outside Team');
});

test('store creates teams, attaches owner role, and generates unique slugs', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    $this->actingAs($firstUser)
        ->post(route('teams.store'), [
            'name' => 'Platform Security',
            'description' => 'Primary security team.',
        ])
        ->assertRedirect();

    $this->actingAs($secondUser)
        ->post(route('teams.store'), [
            'name' => 'Platform Security',
            'description' => 'Secondary security team.',
        ])
        ->assertRedirect();

    $slugs = Team::query()
        ->where('name', 'Platform Security')
        ->orderBy('slug')
        ->pluck('slug')
        ->values()
        ->all();

    expect($slugs)->toBe(['platform-security', 'platform-security-2']);

    $firstTeam = Team::query()->where('slug', 'platform-security')->firstOrFail();
    $secondTeam = Team::query()->where('slug', 'platform-security-2')->firstOrFail();

    expect($firstUser->hasTeamRole($firstTeam, ['owner']))->toBeTrue();
    expect($secondUser->hasTeamRole($secondTeam, ['owner']))->toBeTrue();
});

test('store validates required team name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('teams.store'), [
            'name' => '',
            'description' => 'Invalid payload.',
        ])
        ->assertSessionHasErrors(['name']);
});

test('update requires team authorization and entitlement', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $reserved = Team::factory()->create(['name' => 'Platform Ops', 'slug' => 'platform-ops']);
    $team = Team::factory()->create(['name' => 'Infra Team', 'slug' => 'infra-team']);

    $team->users()->attach($owner->id, ['role' => 'owner']);
    grantActiveSubscription($owner);

    $this->actingAs($outsider)
        ->put(route('teams.update', $team), [
            'name' => 'Platform Ops',
            'description' => 'Unauthorized update attempt.',
        ])
        ->assertForbidden();

    $this->actingAs($owner)
        ->put(route('teams.update', $team), [
            'name' => 'Platform Ops',
            'description' => 'Renamed team.',
        ])
        ->assertRedirect();

    $team->refresh();

    expect($team->name)->toBe('Platform Ops');
    expect($team->slug)->toBe('platform-ops-2');
    expect($reserved->fresh()->slug)->toBe('platform-ops');
});

test('delete allows owners and blocks non-owner members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Operations', 'slug' => 'operations']);

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($member->id, ['role' => 'member']);

    $this->actingAs($member)
        ->delete(route('teams.destroy', $team))
        ->assertForbidden();

    $this->actingAs($owner)
        ->delete(route('teams.destroy', $team))
        ->assertRedirect(route('teams.index'));

    $this->assertDatabaseMissing('teams', [
        'id' => $team->id,
    ]);
});
