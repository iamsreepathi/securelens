<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeadLetterJob;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IngestionFailureController extends Controller
{
    public function index(Request $request): View
    {
        $projectId = $request->string('project_id')->toString();
        $source = strtolower($request->string('source')->toString());

        $failures = DeadLetterJob::query()
            ->with('project:id,name,slug')
            ->when($projectId !== '', fn ($query) => $query->where('project_id', $projectId))
            ->when($source !== '', fn ($query) => $query->where('source', $source))
            ->orderByDesc('failed_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.ingestion-failures.index', [
            'failures' => $failures,
            'filters' => [
                'project_id' => $projectId,
                'source' => $source,
            ],
        ]);
    }
}
