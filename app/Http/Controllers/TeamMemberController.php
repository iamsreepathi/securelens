<?php

namespace App\Http\Controllers;

use App\Http\Requests\RemoveTeamMemberRequest;
use App\Http\Requests\StoreTeamMemberRequest;
use App\Http\Requests\UpdateTeamMemberRoleRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class TeamMemberController extends Controller
{
    public function store(StoreTeamMemberRequest $request, Team $team): RedirectResponse
    {
        $member = User::query()
            ->where('email', $request->string('email')->toString())
            ->firstOrFail();

        if ($team->users()->whereKey($member->getKey())->exists()) {
            return back()->withErrors([
                'email' => 'That user is already a team member.',
            ]);
        }

        $team->users()->attach($member->getKey(), [
            'role' => $request->string('role')->toString(),
        ]);

        return redirect()
            ->route('teams.show', $team)
            ->with('status', 'member-added');
    }

    public function update(UpdateTeamMemberRoleRequest $request, Team $team, User $member): RedirectResponse
    {
        $currentRole = $team->roleForUser($member);

        if ($currentRole === null) {
            abort(404);
        }

        $newRole = $request->string('role')->toString();

        if ($team->wouldRemoveLastAdmin($member, $newRole)) {
            return back()->withErrors([
                'member' => 'At least one admin must remain on the team.',
            ]);
        }

        $team->users()->updateExistingPivot($member->getKey(), [
            'role' => $newRole,
        ]);

        return redirect()
            ->route('teams.show', $team)
            ->with('status', 'member-role-updated');
    }

    public function destroy(RemoveTeamMemberRequest $request, Team $team, User $member): RedirectResponse
    {
        if ($team->roleForUser($member) === null) {
            abort(404);
        }

        if ($team->wouldRemoveLastAdmin($member)) {
            return back()->withErrors([
                'member' => 'At least one admin must remain on the team.',
            ]);
        }

        $team->users()->detach($member->getKey());

        return redirect()
            ->route('teams.show', $team)
            ->with('status', 'member-removed');
    }
}
