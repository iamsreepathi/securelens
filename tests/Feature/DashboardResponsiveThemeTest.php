<?php

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('dashboard template includes responsive readability safeguards for compact viewports', function () {
    $template = file_get_contents(resource_path('views/dashboard.blade.php'));

    expect($template)->not->toBeFalse();
    expect((string) $template)->toContain('dashboard-table-wrap');
    expect((string) $template)->toContain('min-w-[36rem]');
    expect((string) $template)->toContain('sm:min-w-full');
    expect((string) $template)->toContain('dashboard-table-head');
    expect((string) $template)->toContain('sm:text-base');
});

test('dashboard responsive tables and metrics still render correctly with data', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($user->id, ['role' => 'owner']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);

    DB::table('project_vulnerabilities')->insert([
        'id' => (string) Str::uuid(),
        'project_id' => $project->id,
        'osv_url' => 'https://osv.dev/RESP-123',
        'cvss_score' => 7.8,
        'ecosystem' => 'npm',
        'package_name' => 'responsive-kit',
        'version' => '2.0.0',
        'fixed_version' => '2.0.1',
        'source' => 'osv',
        'ingested_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSeeText('Ingestion Freshness');
    $response->assertSeeText('Ecosystem Distribution');
    $response->assertSee('data-test="high-count"', false);
    $response->assertSee('data-test="ecosystem-npm"', false);
});
