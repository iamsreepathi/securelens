<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('teams table has expected columns', function () {
    expect(Schema::hasTable('teams'))->toBeTrue();
    expect(Schema::hasColumns('teams', [
        'id',
        'name',
        'slug',
        'description',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('teams slug is unique', function () {
    DB::table('teams')->insert([
        'id' => (string) Str::uuid(),
        'name' => 'Platform',
        'slug' => 'platform',
        'description' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(function (): void {
        DB::table('teams')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'Duplicate Platform',
            'slug' => 'platform',
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});

test('teams migration rolls back cleanly', function () {
    $migration = require base_path('database/migrations/2026_02_11_055949_create_teams_table.php');
    $migration->down();

    expect(Schema::hasTable('teams'))->toBeFalse();
});
