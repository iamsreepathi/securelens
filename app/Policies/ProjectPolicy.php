<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Support\EntitlementChecker;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->teams()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->canAccessProject($project);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        /** @var array<int, string> $roles */
        $roles = config('rbac.abilities.project.create', []);

        return $user->teams()
            ->wherePivotIn('role', $roles)
            ->exists()
            && app(EntitlementChecker::class)->userHasEntitledTeam($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        /** @var array<int, string> $roles */
        $roles = config('rbac.abilities.project.update', []);

        return $user->hasProjectRole($project, $roles)
            && app(EntitlementChecker::class)->projectIsEntitled($project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        /** @var array<int, string> $roles */
        $roles = config('rbac.abilities.project.delete', []);

        return $user->hasProjectRole($project, $roles);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }
}
