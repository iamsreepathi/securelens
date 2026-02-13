<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::query()
            ->whereHas('teams.users', function (Builder $query) use ($request): void {
                $query->whereKey($request->user()->getKey());
            })
            ->withCount('teams')
            ->orderBy('name')
            ->get();

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Project::class);

        /** @var array<int, string> $roles */
        $roles = config('rbac.abilities.project.create', []);

        $teams = $request->user()
            ->teams()
            ->wherePivotIn('role', $roles)
            ->orderBy('name')
            ->get();

        return view('projects.create', [
            'teams' => $teams,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::query()->create([
            'name' => $request->string('name')->toString(),
            'description' => $request->string('description')->toString() ?: null,
            'is_active' => true,
        ]);

        $project->teams()->syncWithoutDetaching([
            $request->string('team_id')->toString() => ['assigned_at' => now()],
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'project-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $project->load([
            'teams' => fn ($query) => $query->orderBy('name'),
        ]);

        /** @var array<int, string> $roles */
        $roles = config('rbac.abilities.project.create', []);

        $assignableTeams = $request->user()
            ->teams()
            ->wherePivotIn('role', $roles)
            ->whereNotIn('teams.id', $project->teams->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('projects.show', [
            'project' => $project,
            'assignableTeams' => $assignableTeams,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->fill($request->validated());
        $project->save();

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'project-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->forceFill([
            'is_active' => false,
        ])->save();

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'project-archived');
    }
}
