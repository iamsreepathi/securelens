<?php

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function insertVulnerability(array $attributes): void
{
    DB::table('project_vulnerabilities')->insert(array_merge([
        'id' => (string) Str::uuid(),
        'osv_url' => 'https://osv.dev/TEST-123',
        'ecosystem' => 'npm',
        'package_name' => 'demo-package',
        'version' => '1.0.0',
        'fixed_version' => null,
        'source' => 'osv',
        'ingested_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ], $attributes));
}

test('dashboard severity and ecosystem aggregates match scoped vulnerability dataset', function () {
    $user = User::factory()->create();
    $outsider = User::factory()->create();
    $team = Team::factory()->create();
    $outsiderTeam = Team::factory()->create();
    $projectA = Project::factory()->create(['name' => 'A']);
    $projectB = Project::factory()->create(['name' => 'B']);
    $outsiderProject = Project::factory()->create(['name' => 'Outside']);

    $team->users()->attach($user->id, ['role' => 'owner']);
    $outsiderTeam->users()->attach($outsider->id, ['role' => 'owner']);
    $projectA->teams()->attach($team->id, ['assigned_at' => now()]);
    $projectB->teams()->attach($team->id, ['assigned_at' => now()]);
    $outsiderProject->teams()->attach($outsiderTeam->id, ['assigned_at' => now()]);

    insertVulnerability(['project_id' => $projectA->id, 'cvss_score' => 9.8, 'ecosystem' => 'npm']);
    insertVulnerability(['project_id' => $projectA->id, 'cvss_score' => 8.2, 'ecosystem' => 'npm']);
    insertVulnerability(['project_id' => $projectB->id, 'cvss_score' => 5.1, 'ecosystem' => 'pypi']);
    insertVulnerability(['project_id' => $projectB->id, 'cvss_score' => 2.4, 'ecosystem' => 'pypi']);
    insertVulnerability(['project_id' => $projectB->id, 'cvss_score' => null, 'ecosystem' => 'maven']);
    insertVulnerability(['project_id' => $outsiderProject->id, 'cvss_score' => 9.9, 'ecosystem' => 'nuget']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertViewHas('summary', function (array $summary): bool {
        return $summary['critical'] === 1
            && $summary['high'] === 1
            && $summary['medium'] === 1
            && $summary['low'] === 2
            && $summary['total'] === 5;
    });
    $response->assertViewHas('ecosystemDistribution', function ($distribution): bool {
        $counts = $distribution
            ->mapWithKeys(fn ($row) => [$row->ecosystem => (int) $row->vulnerability_count])
            ->all();

        return $counts === [
            'npm' => 2,
            'pypi' => 2,
            'maven' => 1,
        ];
    });
});

test('dashboard shows integration empty state when user has no projects', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Connect your first project')
        ->assertSeeText('Create a project and configure your vulnerability ingestion integration');
});
