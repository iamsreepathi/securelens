<?php

use App\Models\Team;
use App\Models\User;

test('owners can add members with a role assignment', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Blue Team']);
    $team->users()->attach($owner->id, ['role' => 'owner']);

    $this->actingAs($owner)
        ->post(route('teams.members.store', $team), [
            'email' => $member->email,
            'role' => 'team_admin',
        ])
        ->assertRedirect(route('teams.show', $team));

    expect($member->hasTeamRole($team, ['team_admin']))->toBeTrue();
});

test('duplicate memberships are rejected', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Blue Team']);
    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($member->id, ['role' => 'member']);

    $this->actingAs($owner)
        ->post(route('teams.members.store', $team), [
            'email' => $member->email,
            'role' => 'team_admin',
        ])
        ->assertSessionHasErrors(['email']);
});

test('owners can update member roles', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Blue Team']);
    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($member->id, ['role' => 'member']);

    $this->actingAs($owner)
        ->put(route('teams.members.update', [$team, $member]), [
            'role' => 'team_admin',
        ])
        ->assertRedirect(route('teams.show', $team));

    expect($member->hasTeamRole($team, ['team_admin']))->toBeTrue();
});

test('guardrails prevent removing or demoting the last admin', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Blue Team']);

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($member->id, ['role' => 'member']);

    $this->actingAs($owner)
        ->put(route('teams.members.update', [$team, $owner]), [
            'role' => 'member',
        ])
        ->assertSessionHasErrors(['member']);

    $this->actingAs($owner)
        ->delete(route('teams.members.destroy', [$team, $owner]))
        ->assertSessionHasErrors(['member']);

    expect($owner->hasTeamRole($team, ['owner']))->toBeTrue();
});

test('non managers cannot manage team memberships', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Blue Team']);

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($member->id, ['role' => 'member']);

    $this->actingAs($member)
        ->post(route('teams.members.store', $team), [
            'email' => User::factory()->create()->email,
            'role' => 'member',
        ])
        ->assertForbidden();

    $this->actingAs($member)
        ->put(route('teams.members.update', [$team, $owner]), [
            'role' => 'member',
        ])
        ->assertForbidden();

    $this->actingAs($member)
        ->delete(route('teams.members.destroy', [$team, $owner]))
        ->assertForbidden();
});
