<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('project_team table has expected columns', function () {
    expect(Schema::hasTable('project_team'))->toBeTrue();
    expect(Schema::hasColumns('project_team', [
        'project_id',
        'team_id',
        'assigned_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('project_team enforces foreign keys', function () {
    expect(function (): void {
        DB::table('project_team')->insert([
            'project_id' => (string) Str::uuid(),
            'team_id' => (string) Str::uuid(),
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});

test('project_team prevents duplicate links via composite primary key', function () {
    $projectId = (string) Str::uuid();
    $teamId = (string) Str::uuid();

    DB::table('projects')->insert([
        'id' => $projectId,
        'name' => 'Scanner Project',
        'slug' => 'scanner-project',
        'description' => null,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('teams')->insert([
        'id' => $teamId,
        'name' => 'Security Team',
        'slug' => 'security-team',
        'description' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('project_team')->insert([
        'project_id' => $projectId,
        'team_id' => $teamId,
        'assigned_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(function () use ($projectId, $teamId): void {
        DB::table('project_team')->insert([
            'project_id' => $projectId,
            'team_id' => $teamId,
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});

test('project_team has index on team_id and project_id', function () {
    expect(Schema::hasIndex('project_team', ['team_id', 'project_id']))->toBeTrue();
});

test('project_team migration rolls back cleanly', function () {
    $migration = require base_path('database/migrations/2026_02_11_060702_create_project_team_table.php');
    $migration->down();

    expect(Schema::hasTable('project_team'))->toBeFalse();
});
