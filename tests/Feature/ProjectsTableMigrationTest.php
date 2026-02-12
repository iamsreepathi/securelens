<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('projects table has expected columns', function () {
    expect(Schema::hasTable('projects'))->toBeTrue();
    expect(Schema::hasColumns('projects', [
        'id',
        'name',
        'slug',
        'description',
        'is_active',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('projects slug is unique', function () {
    DB::table('projects')->insert([
        'id' => (string) Str::uuid(),
        'name' => 'Core Platform',
        'slug' => 'core-platform',
        'description' => null,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(function (): void {
        DB::table('projects')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'Duplicate Core Platform',
            'slug' => 'core-platform',
            'description' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});

test('projects is_active defaults to true', function () {
    $projectId = (string) Str::uuid();

    DB::table('projects')->insert([
        'id' => $projectId,
        'name' => 'Default Active Project',
        'slug' => 'default-active-project',
        'description' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $isActive = DB::table('projects')->where('id', $projectId)->value('is_active');

    expect((bool) $isActive)->toBeTrue();
});

test('projects migration rolls back cleanly', function () {
    $migration = require base_path('database/migrations/2026_02_11_060255_create_projects_table.php');
    $migration->down();

    expect(Schema::hasTable('projects'))->toBeFalse();
});
