<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('team_user table has expected columns', function () {
    expect(Schema::hasTable('team_user'))->toBeTrue();
    expect(Schema::hasColumns('team_user', [
        'team_id',
        'user_id',
        'role',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('team_user enforces foreign keys', function () {
    expect(function (): void {
        DB::table('team_user')->insert([
            'team_id' => (string) Str::uuid(),
            'user_id' => (string) Str::uuid(),
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});

test('team_user prevents duplicate memberships via composite primary key', function () {
    $teamId = (string) Str::uuid();
    $userId = (string) Str::uuid();

    DB::table('teams')->insert([
        'id' => $teamId,
        'name' => 'App Team',
        'slug' => 'app-team',
        'description' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'id' => $userId,
        'name' => 'Dev User',
        'email' => 'dev-user@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
        'remember_token' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('team_user')->insert([
        'team_id' => $teamId,
        'user_id' => $userId,
        'role' => 'member',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(function () use ($teamId, $userId): void {
        DB::table('team_user')->insert([
            'team_id' => $teamId,
            'user_id' => $userId,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});

test('team_user role defaults to member', function () {
    $teamId = (string) Str::uuid();
    $userId = (string) Str::uuid();

    DB::table('teams')->insert([
        'id' => $teamId,
        'name' => 'Ops Team',
        'slug' => 'ops-team',
        'description' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'id' => $userId,
        'name' => 'Ops User',
        'email' => 'ops-user@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
        'remember_token' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('team_user')->insert([
        'team_id' => $teamId,
        'user_id' => $userId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $role = DB::table('team_user')
        ->where('team_id', $teamId)
        ->where('user_id', $userId)
        ->value('role');

    expect($role)->toBe('member');
});

test('team_user has index on user_id and role', function () {
    expect(Schema::hasIndex('team_user', ['user_id', 'role']))->toBeTrue();
});

test('team_user migration rolls back cleanly', function () {
    $migration = require base_path('database/migrations/2026_02_11_060513_create_team_user_table.php');
    $migration->down();

    expect(Schema::hasTable('team_user'))->toBeFalse();
});
