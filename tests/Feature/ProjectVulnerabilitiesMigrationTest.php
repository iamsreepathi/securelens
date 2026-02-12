<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('project_vulnerabilities table has expected columns', function () {
    expect(Schema::hasTable('project_vulnerabilities'))->toBeTrue();
    expect(Schema::hasColumns('project_vulnerabilities', [
        'id',
        'project_id',
        'osv_url',
        'cvss_score',
        'ecosystem',
        'package_name',
        'version',
        'fixed_version',
        'source',
        'ingested_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('project_vulnerabilities enforces project foreign key', function () {
    expect(function (): void {
        DB::table('project_vulnerabilities')->insert([
            'id' => (string) Str::uuid(),
            'project_id' => (string) Str::uuid(),
            'osv_url' => 'https://osv.dev/vulnerability/TEST-0001',
            'cvss_score' => 7.5,
            'ecosystem' => 'packagist',
            'package_name' => 'vendor/package',
            'version' => '1.0.0',
            'fixed_version' => null,
            'source' => 'osv',
            'ingested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});

test('project_vulnerabilities supports nullable cvss_score', function () {
    $projectId = (string) Str::uuid();

    DB::table('projects')->insert([
        'id' => $projectId,
        'name' => 'Vuln Scanner',
        'slug' => 'vuln-scanner',
        'description' => null,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('project_vulnerabilities')->insert([
        'id' => (string) Str::uuid(),
        'project_id' => $projectId,
        'osv_url' => 'https://osv.dev/vulnerability/TEST-0002',
        'cvss_score' => null,
        'ecosystem' => 'packagist',
        'package_name' => 'vendor/package-two',
        'version' => '2.0.0',
        'fixed_version' => null,
        'source' => 'osv',
        'ingested_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cvssScore = DB::table('project_vulnerabilities')->value('cvss_score');

    expect($cvssScore)->toBeNull();
});

test('project_vulnerabilities has required indexes', function () {
    expect(Schema::hasIndex('project_vulnerabilities', ['project_id', 'ecosystem']))->toBeTrue();
    expect(Schema::hasIndex('project_vulnerabilities', ['project_id', 'source']))->toBeTrue();
});

test('project_vulnerabilities migration rolls back cleanly', function () {
    $migration = require base_path('database/migrations/2026_02_11_060836_create_project_vulnerabilities_table.php');
    $migration->down();

    expect(Schema::hasTable('project_vulnerabilities'))->toBeFalse();
});
