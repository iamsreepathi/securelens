<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, HasFactory, HasUuids, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * @return BelongsToMany<Team, $this>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function roleForTeam(Team $team): ?string
    {
        /** @var string|null $role */
        $role = $this->teams()
            ->whereKey($team->getKey())
            ->value('team_user.role');

        return $role;
    }

    public function hasTeamRole(Team $team, array $roles): bool
    {
        return $this->teams()
            ->whereKey($team->getKey())
            ->wherePivotIn('role', $roles)
            ->exists();
    }

    public function canAccessProject(Project $project): bool
    {
        return $this->teams()
            ->whereHas('projects', function (Builder $query) use ($project): void {
                $query->whereKey($project->getKey());
            })
            ->exists();
    }

    public function hasProjectRole(Project $project, array $roles): bool
    {
        return $this->teams()
            ->wherePivotIn('role', $roles)
            ->whereHas('projects', function (Builder $query) use ($project): void {
                $query->whereKey($project->getKey());
            })
            ->exists();
    }

    public function isAdmin(): bool
    {
        /** @var array<int, string> $adminRoles */
        $adminRoles = config('rbac.roles.admin', []);

        return $this->teams()
            ->wherePivotIn('role', $adminRoles)
            ->exists();
    }
}
