<?php

namespace App\Http\Controllers;

use App\Http\Requests\RemoveProjectTeamAssignmentRequest;
use App\Http\Requests\StoreProjectTeamAssignmentRequest;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ProjectTeamAssignmentController extends Controller
{
    public function store(StoreProjectTeamAssignmentRequest $request, Project $project): RedirectResponse
    {
        $teamId = $request->string('team_id')->toString();

        if ($project->teams()->whereKey($teamId)->exists()) {
            return back()->withErrors([
                'team_id' => 'That team is already assigned to this project.',
            ]);
        }

        $project->teams()->attach($teamId, [
            'assigned_at' => now(),
        ]);

        Log::info('project.assignment.changed', [
            'action' => 'assigned',
            'project_id' => $project->id,
            'team_id' => $teamId,
            'actor_id' => $request->user()?->getKey(),
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'project-team-assigned');
    }

    public function destroy(RemoveProjectTeamAssignmentRequest $request, Project $project, Team $team): RedirectResponse
    {
        if (! $project->teams()->whereKey($team->getKey())->exists()) {
            abort(404);
        }

        if ($project->teams()->count() <= 1) {
            return back()->withErrors([
                'team' => 'A project must remain assigned to at least one team.',
            ]);
        }

        $project->teams()->detach($team->getKey());

        Log::info('project.assignment.changed', [
            'action' => 'unassigned',
            'project_id' => $project->id,
            'team_id' => $team->id,
            'actor_id' => $request->user()?->getKey(),
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'project-team-unassigned');
    }
}
