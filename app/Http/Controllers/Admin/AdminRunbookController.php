<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminRunbookController extends Controller
{
    public function index(): View
    {
        $runbooks = collect(config('operations.runbooks', []))
            ->map(function (array $runbook, string $key): array {
                return [
                    'key' => $key,
                    'title' => $runbook['title'],
                    'version' => $runbook['version'],
                    'objective' => $runbook['objective'],
                    'triage_steps' => $runbook['triage_steps'],
                    'escalation' => $runbook['escalation'],
                    'rollback' => $runbook['rollback'],
                ];
            })
            ->values();

        return view('admin.runbooks.index', [
            'runbooks' => $runbooks,
            'runbookCount' => $runbooks->count(),
        ]);
    }
}
