<?php

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('dashboard template includes semantic table accessibility hooks and avoids dim text utility regressions', function () {
    $template = file_get_contents(resource_path('views/dashboard.blade.php'));

    expect($template)->not->toBeFalse();
    expect((string) $template)->toContain('scope="col"');
    expect((string) $template)->toContain('Project ingestion freshness table');
    expect((string) $template)->not->toContain('text-zinc-500');
    expect((string) $template)->not->toContain('text-zinc-600');
});

test('dashboard renders core contrast-critical indicators for authenticated users', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $project = Project::factory()->create();

    $team->users()->attach($user->id, ['role' => 'owner']);
    $project->teams()->attach($team->id, ['assigned_at' => now()]);

    DB::table('project_vulnerabilities')->insert([
        'id' => (string) Str::uuid(),
        'project_id' => $project->id,
        'osv_url' => 'https://osv.dev/EXAMPLE-123',
        'cvss_score' => 9.5,
        'ecosystem' => 'npm',
        'package_name' => 'demo',
        'version' => '1.0.0',
        'fixed_version' => null,
        'source' => 'osv',
        'ingested_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSeeText('Security Overview');
    $response->assertSee('data-test="critical-count"', false);
    $response->assertSee('data-test="ecosystem-npm"', false);
});
