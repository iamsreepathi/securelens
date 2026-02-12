<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'admin'])->get('/__test/admin-area', function () {
        return response()->json(['ok' => true]);
    });
});

test('admin middleware allows owner and team_admin roles', function (string $role) {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['role' => $role]);

    $this->actingAs($user)
        ->get('/__test/admin-area')
        ->assertOk()
        ->assertJson(['ok' => true]);
})->with(['owner', 'team_admin']);

test('admin middleware denies member role', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['role' => 'member']);

    $this->actingAs($user)
        ->get('/__test/admin-area')
        ->assertForbidden();
});

test('admin middleware keeps default auth behavior for guests', function () {
    $this->get('/__test/admin-area')
        ->assertRedirect('/login');
});
