<?php

namespace App\Support;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;

class EntitlementChecker
{
    public function teamIsEntitled(Team $team): bool
    {
        return $team->users()
            ->wherePivot('role', 'owner')
            ->get()
            ->contains(fn (User $owner): bool => $this->userIsEntitled($owner));
    }

    public function projectIsEntitled(Project $project): bool
    {
        return $project->teams()
            ->get()
            ->contains(fn (Team $team): bool => $this->teamIsEntitled($team));
    }

    public function userHasEntitledTeam(User $user): bool
    {
        return $user->teams()
            ->get()
            ->contains(fn (Team $team): bool => $this->teamIsEntitled($team));
    }

    public function userIsEntitled(User $user): bool
    {
        if ($user->subscribed('default')) {
            return true;
        }

        return (bool) $user->subscription('default')?->onGracePeriod();
    }
}
