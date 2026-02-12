<?php

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'entitled'])->get('/__test/entitled-action', function () {
        return response('ok');
    });
});

function createEntitledProjectContext(string $subscriptionStatus, ?string $graceEndsAt = null): array
{
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($user->id, ['role' => 'owner']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);

    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_'.Str::random(14),
        'stripe_status' => $subscriptionStatus,
        'stripe_price' => 'price_team',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => $graceEndsAt,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return compact('user', 'team', 'project');
}

test('active subscriptions allow restricted team and project updates', function () {
    $context = createEntitledProjectContext('active');
    $user = $context['user'];
    $team = $context['team'];
    $project = $context['project'];

    expect($user->can('update', $team))->toBeTrue();
    expect($user->can('update', $project))->toBeTrue();
});

test('grace-period subscriptions allow restricted updates', function () {
    $context = createEntitledProjectContext('canceled', now()->addDay()->toDateTimeString());
    $user = $context['user'];
    $team = $context['team'];
    $project = $context['project'];

    expect($user->can('update', $team))->toBeTrue();
    expect($user->can('update', $project))->toBeTrue();
});

test('expired subscriptions block restricted updates', function () {
    $context = createEntitledProjectContext('canceled', now()->subDay()->toDateTimeString());
    $user = $context['user'];
    $team = $context['team'];
    $project = $context['project'];

    expect($user->can('update', $team))->toBeFalse();
    expect($user->can('update', $project))->toBeFalse();
});

test('inactive accounts cannot create restricted resources', function () {
    $context = createEntitledProjectContext('canceled', now()->subDay()->toDateTimeString());
    $user = $context['user'];

    expect($user->can('create', Team::class))->toBeFalse();
    expect($user->can('create', Project::class))->toBeFalse();
});

test('entitlement middleware returns consistent blocked UX', function () {
    $inactiveContext = createEntitledProjectContext('canceled', now()->subDay()->toDateTimeString());
    $activeContext = createEntitledProjectContext('active');

    $this->actingAs($inactiveContext['user'])
        ->get('/__test/entitled-action')
        ->assertRedirect(route('billing.edit'))
        ->assertSessionHas('status', 'subscription-required');

    $this->actingAs($activeContext['user'])
        ->get('/__test/entitled-action')
        ->assertOk();
});
