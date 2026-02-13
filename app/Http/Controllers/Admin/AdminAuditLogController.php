<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminOperationalAction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'action' => $request->string('action')->toString(),
            'actor_id' => $request->string('actor_id')->toString(),
            'target_type' => $request->string('target_type')->toString(),
        ];

        $logs = AdminOperationalAction::query()
            ->with('actor:id,name,email')
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery->where('action', 'like', '%'.$search.'%')
                        ->orWhere('target_type', 'like', '%'.$search.'%')
                        ->orWhere('target_id', 'like', '%'.$search.'%')
                        ->orWhereHas('actor', function ($actorQuery) use ($search): void {
                            $actorQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($filters['action'] !== '', fn ($query) => $query->where('action', $filters['action']))
            ->when($filters['actor_id'] !== '', fn ($query) => $query->where('actor_user_id', $filters['actor_id']))
            ->when($filters['target_type'] !== '', fn ($query) => $query->where('target_type', $filters['target_type']))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $actionOptions = AdminOperationalAction::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $targetTypeOptions = AdminOperationalAction::query()
            ->select('target_type')
            ->distinct()
            ->orderBy('target_type')
            ->pluck('target_type');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'actionOptions' => $actionOptions,
            'targetTypeOptions' => $targetTypeOptions,
        ]);
    }
}
