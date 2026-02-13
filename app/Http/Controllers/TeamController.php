<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Team::class);

        $teams = $request->user()
            ->teams()
            ->withCount(['users', 'projects'])
            ->orderBy('name')
            ->get();

        return view('teams.index', [
            'teams' => $teams,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Team::class);

        return view('teams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $team = Team::query()->create($request->validated());
        $team->users()->syncWithoutDetaching([
            $request->user()->getKey() => ['role' => 'owner'],
        ]);

        return redirect()
            ->route('teams.show', $team)
            ->with('status', 'team-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team): View
    {
        $this->authorize('view', $team);

        $team->loadCount(['users', 'projects'])
            ->load([
                'users' => fn ($query) => $query->orderBy('name'),
            ]);

        return view('teams.show', [
            'team' => $team,
            'assignableRoles' => $team->assignableRoles(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Team $team): View
    {
        $this->authorize('update', $team);

        return view('teams.edit', [
            'team' => $team,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $team->fill($request->validated());
        $team->save();

        return redirect()
            ->route('teams.show', $team)
            ->with('status', 'team-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team): RedirectResponse
    {
        $this->authorize('delete', $team);

        $team->delete();

        return redirect()
            ->route('teams.index')
            ->with('status', 'team-deleted');
    }
}
