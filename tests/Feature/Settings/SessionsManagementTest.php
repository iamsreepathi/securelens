<?php

use App\Livewire\Settings\Sessions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;

test('sessions page can be rendered for authenticated users', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('sessions.edit'))
        ->assertOk();
});

test('sessions component lists active sessions for the current user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    DB::table('sessions')->insert([
        [
            'id' => 'session-current',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Current Browser',
            'payload' => 'payload-current',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'session-other',
            'user_id' => $user->id,
            'ip_address' => '10.10.10.2',
            'user_agent' => 'Other Browser',
            'payload' => 'payload-other',
            'last_activity' => now()->subMinute()->timestamp,
        ],
    ]);

    $sessions = Livewire::test(Sessions::class)
        ->get('sessions');

    expect($sessions)->toHaveCount(2);
});

test('logout other sessions removes non-current sessions and logs audit event', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);
    $this->actingAs($user);
    Log::spy();

    DB::table('sessions')->insert([
        [
            'id' => session()->getId(),
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Current Browser',
            'payload' => 'payload-current',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'session-other',
            'user_id' => $user->id,
            'ip_address' => '10.10.10.2',
            'user_agent' => 'Other Browser',
            'payload' => 'payload-other',
            'last_activity' => now()->subMinute()->timestamp,
        ],
    ]);

    Livewire::test(Sessions::class)
        ->set('current_password', 'password')
        ->call('logoutOtherSessions')
        ->assertHasNoErrors();

    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('sessions')->where('id', session()->getId())->exists())->toBeTrue();

    Log::shouldHaveReceived('info')->once()->with(
        'security.sessions.logout_other_devices',
        [
            'user_id' => $user->id,
            'retained_session_id' => session()->getId(),
        ],
    );
});
