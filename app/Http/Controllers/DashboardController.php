<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $projectIds = Project::query()
            ->whereHas('teams.users', function ($query) use ($request): void {
                $query->whereKey($request->user()->getKey());
            })
            ->pluck('projects.id');

        $summary = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'total' => 0,
            'latest_ingested_at' => null,
        ];

        $ecosystemDistribution = collect();
        $projectFreshness = collect();

        if ($projectIds->isNotEmpty()) {
            $summaryRow = DB::table('project_vulnerabilities')
                ->whereIn('project_id', $projectIds)
                ->selectRaw('SUM(CASE WHEN cvss_score >= 9.0 THEN 1 ELSE 0 END) as critical_count')
                ->selectRaw('SUM(CASE WHEN cvss_score >= 7.0 AND cvss_score < 9.0 THEN 1 ELSE 0 END) as high_count')
                ->selectRaw('SUM(CASE WHEN cvss_score >= 4.0 AND cvss_score < 7.0 THEN 1 ELSE 0 END) as medium_count')
                ->selectRaw('SUM(CASE WHEN cvss_score < 4.0 OR cvss_score IS NULL THEN 1 ELSE 0 END) as low_count')
                ->selectRaw('COUNT(*) as total_count')
                ->selectRaw('MAX(ingested_at) as latest_ingested_at')
                ->first();

            $summary = [
                'critical' => (int) ($summaryRow->critical_count ?? 0),
                'high' => (int) ($summaryRow->high_count ?? 0),
                'medium' => (int) ($summaryRow->medium_count ?? 0),
                'low' => (int) ($summaryRow->low_count ?? 0),
                'total' => (int) ($summaryRow->total_count ?? 0),
                'latest_ingested_at' => $summaryRow?->latest_ingested_at,
            ];

            $ecosystemDistribution = DB::table('project_vulnerabilities')
                ->whereIn('project_id', $projectIds)
                ->select('ecosystem', DB::raw('COUNT(*) as vulnerability_count'))
                ->groupBy('ecosystem')
                ->orderByDesc('vulnerability_count')
                ->orderBy('ecosystem')
                ->get();

            $projectIngestion = DB::table('project_vulnerabilities')
                ->whereIn('project_id', $projectIds)
                ->select('project_id', DB::raw('COUNT(*) as vulnerability_count'), DB::raw('MAX(ingested_at) as last_ingested_at'))
                ->groupBy('project_id');

            $projectFreshness = Project::query()
                ->whereIn('projects.id', $projectIds)
                ->leftJoinSub($projectIngestion, 'project_ingestion', function ($join): void {
                    $join->on('project_ingestion.project_id', '=', 'projects.id');
                })
                ->orderBy('projects.name')
                ->get([
                    'projects.id',
                    'projects.name',
                    'projects.slug',
                    'projects.is_active',
                    'project_ingestion.vulnerability_count',
                    'project_ingestion.last_ingested_at',
                ])
                ->map(function (Project $project): array {
                    $lastIngestedAt = $project->getAttribute('last_ingested_at');
                    $lastIngestedAtCarbon = $lastIngestedAt !== null
                        ? CarbonImmutable::parse((string) $lastIngestedAt)
                        : null;

                    $freshness = 'never';

                    if ($lastIngestedAtCarbon !== null) {
                        $freshness = $lastIngestedAtCarbon->lt(now()->subDay()) ? 'stale' : 'fresh';
                    }

                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'slug' => $project->slug,
                        'is_active' => (bool) $project->is_active,
                        'vulnerability_count' => (int) ($project->getAttribute('vulnerability_count') ?? 0),
                        'last_ingested_at' => $lastIngestedAtCarbon,
                        'freshness' => $freshness,
                    ];
                });
        }

        return view('dashboard', [
            'summary' => $summary,
            'ecosystemDistribution' => $ecosystemDistribution,
            'projectFreshness' => $projectFreshness,
            'hasProjects' => $projectIds->isNotEmpty(),
        ]);
    }
}
