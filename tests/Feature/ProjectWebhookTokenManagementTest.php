<?php

use App\Models\Project;
use App\Models\ProjectWebhookToken;
use App\Models\Team;
use App\Models\User;
use App\Support\WebhookTokenManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function grantProjectEntitlement(User $owner): void
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

test('team admins can create and securely store project webhook tokens', function () {
    $owner = User::factory()->create();
    $teamAdmin = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($teamAdmin->id, ['role' => 'team_admin']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);
    grantProjectEntitlement($owner);

    $response = $this->actingAs($teamAdmin)
        ->postJson(route('projects.webhook-tokens.store', $project), [
            'name' => 'CI Ingestion Token',
        ])
        ->assertCreated();

    $plainTextToken = $response->json('plain_text_token');
    $tokenId = $response->json('id');

    expect($plainTextToken)->toStartWith('sl_ing_');

    $token = ProjectWebhookToken::query()->findOrFail($tokenId);
    expect($token->token_hash)->toBe(hash('sha256', $plainTextToken));
    expect($token->token_hash)->not->toBe($plainTextToken);

    $resolved = app(WebhookTokenManager::class)->resolveActiveToken($project, $plainTextToken);
    expect($resolved?->id)->toBe($token->id);
});

test('rotation revokes old token immediately and returns a new active token', function () {
    $owner = User::factory()->create();
    $teamAdmin = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($teamAdmin->id, ['role' => 'team_admin']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);
    grantProjectEntitlement($owner);

    $initial = $this->actingAs($teamAdmin)
        ->postJson(route('projects.webhook-tokens.store', $project), [
            'name' => 'CI Token',
        ])
        ->assertCreated();

    $oldPlainTextToken = $initial->json('plain_text_token');
    $oldToken = ProjectWebhookToken::query()->findOrFail($initial->json('id'));

    $rotated = $this->actingAs($teamAdmin)
        ->postJson(route('projects.webhook-tokens.rotate', [$project, $oldToken]))
        ->assertOk();

    $newPlainTextToken = $rotated->json('plain_text_token');
    $newToken = ProjectWebhookToken::query()->findOrFail($rotated->json('id'));

    expect($oldToken->fresh()?->revoked_at)->not->toBeNull();
    expect($newToken->rotated_from_id)->toBe($oldToken->id);

    $manager = app(WebhookTokenManager::class);
    expect($manager->resolveActiveToken($project, $oldPlainTextToken))->toBeNull();
    expect($manager->resolveActiveToken($project, $newPlainTextToken)?->id)->toBe($newToken->id);
});

test('revoked tokens are rejected immediately', function () {
    $owner = User::factory()->create();
    $teamAdmin = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $team->users()->attach($teamAdmin->id, ['role' => 'team_admin']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);
    grantProjectEntitlement($owner);

    $issued = $this->actingAs($teamAdmin)
        ->postJson(route('projects.webhook-tokens.store', $project), [
            'name' => 'CI Token',
        ])
        ->assertCreated();

    $plainTextToken = $issued->json('plain_text_token');
    $token = ProjectWebhookToken::query()->findOrFail($issued->json('id'));

    $this->actingAs($teamAdmin)
        ->deleteJson(route('projects.webhook-tokens.destroy', [$project, $token]))
        ->assertOk()
        ->assertJsonPath('status', 'revoked');

    expect($token->fresh()?->revoked_at)->not->toBeNull();
    expect(app(WebhookTokenManager::class)->resolveActiveToken($project, $plainTextToken))->toBeNull();
});

test('unauthorized users cannot manage webhook tokens', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($owner->id, ['role' => 'owner']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);
    grantProjectEntitlement($owner);

    $this->actingAs($outsider)
        ->postJson(route('projects.webhook-tokens.store', $project), [
            'name' => 'Blocked',
        ])
        ->assertForbidden();
});
