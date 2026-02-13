<?php

use App\Models\Team;
use App\Models\User;

test('admin can view versioned operations runbooks for ingestion and queue incidents', function () {
    $admin = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($admin->id, ['role' => 'owner']);

    $response = $this->actingAs($admin)->get(route('admin.runbooks.index'));

    $response->assertOk();
    $response->assertSeeText('Operations Runbooks');
    $response->assertSeeText('Webhook Failure Triage');
    $response->assertSeeText('Queue Backlog and Failure Recovery');
    $response->assertSeeText('Published runbooks: 2');
});

test('runbook view remains restricted to admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.runbooks.index'))
        ->assertForbidden();
});
