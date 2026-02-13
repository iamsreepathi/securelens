<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeadLetterJob;
use App\Models\IngestionRun;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $now = CarbonImmutable::now();
        $windowStart = $now->subDays(6)->startOfDay();
        $nowTimestamp = $now->timestamp;

        $tenantSummary = [
            'teams_total' => Team::query()->count(),
            'projects_total' => Project::query()->count(),
            'projects_active' => Project::query()->where('is_active', true)->count(),
            'users_total' => User::query()->count(),
            'users_with_team_access' => DB::table('team_user')->distinct('user_id')->count('user_id'),
        ];

        $runsTotal = IngestionRun::query()->count();
        $runsProcessed = IngestionRun::query()->whereNotNull('processed_at')->count();

        $ingestionSummary = [
            'runs_total' => $runsTotal,
            'runs_processed' => $runsProcessed,
            'runs_pending' => $runsTotal - $runsProcessed,
            'dead_letter_total' => DeadLetterJob::query()->count(),
            'success_rate' => $runsTotal > 0
                ? round(($runsProcessed / $runsTotal) * 100, 1)
                : null,
        ];

        $successByDate = IngestionRun::query()
            ->whereNotNull('processed_at')
            ->where('ingested_at', '>=', $windowStart)
            ->selectRaw('DATE(ingested_at) as day')
            ->selectRaw('COUNT(*) as run_count')
            ->groupByRaw('DATE(ingested_at)')
            ->pluck('run_count', 'day');

        $failureByDate = DeadLetterJob::query()
            ->where('failed_at', '>=', $windowStart)
            ->selectRaw('DATE(failed_at) as day')
            ->selectRaw('COUNT(*) as failure_count')
            ->groupByRaw('DATE(failed_at)')
            ->pluck('failure_count', 'day');

        $ingestionTrend = collect(range(0, 6))
            ->map(function (int $offset) use ($windowStart, $successByDate, $failureByDate): array {
                $date = $windowStart->addDays($offset)->toDateString();

                return [
                    'date' => $date,
                    'label' => $windowStart->addDays($offset)->format('M j'),
                    'success_count' => (int) ($successByDate[$date] ?? 0),
                    'failure_count' => (int) ($failureByDate[$date] ?? 0),
                ];
            });

        $queueDepths = DB::table('jobs')
            ->select('queue')
            ->selectRaw('COUNT(*) as depth')
            ->selectRaw('MAX(attempts) as max_attempts')
            ->groupBy('queue')
            ->orderByDesc('depth')
            ->orderBy('queue')
            ->get()
            ->map(fn (object $row): array => [
                'queue' => (string) $row->queue,
                'depth' => (int) $row->depth,
                'max_attempts' => (int) $row->max_attempts,
            ]);

        $queueHealth = [
            'pending_jobs_total' => DB::table('jobs')->count(),
            'ingestion_queue_depth' => DB::table('jobs')->where('queue', 'ingestion')->count(),
            'delayed_jobs_total' => DB::table('jobs')->where('available_at', '>', $nowTimestamp)->count(),
            'retrying_jobs_total' => DB::table('jobs')->where('attempts', '>', 1)->count(),
            'failed_jobs_last_24h' => DB::table('failed_jobs')->where('failed_at', '>=', $now->subDay())->count(),
            'dead_letters_last_24h' => DeadLetterJob::query()->where('failed_at', '>=', $now->subDay())->count(),
            'queue_depths' => $queueDepths,
        ];

        return view('admin.dashboard', [
            'tenantSummary' => $tenantSummary,
            'ingestionSummary' => $ingestionSummary,
            'ingestionTrend' => $ingestionTrend,
            'queueHealth' => $queueHealth,
        ]);
    }
}
