<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected static function booted(): void
    {
        static::saving(function (Team $team): void {
            if ($team->isDirty('name') || blank($team->slug)) {
                $team->slug = static::generateUniqueSlug($team->name, $team->getKey());
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function generateUniqueSlug(string $name, string|int|null $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'team';
        }

        $existingSlugs = static::query()
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where(function ($query) use ($baseSlug): void {
                $query->where('slug', $baseSlug)
                    ->orWhere('slug', 'like', $baseSlug.'-%');
            })
            ->pluck('slug');

        if (! $existingSlugs->contains($baseSlug)) {
            return $baseSlug;
        }

        $highestSuffix = 1;

        foreach ($existingSlugs as $slug) {
            if (preg_match('/^'.preg_quote($baseSlug, '/').'-(\d+)$/', $slug, $matches) !== 1) {
                continue;
            }

            $highestSuffix = max($highestSuffix, (int) $matches[1]);
        }

        return $baseSlug.'-'.($highestSuffix + 1);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /**
     * @return list<string>
     */
    public function assignableRoles(): array
    {
        $roles = config('rbac.abilities.team.view', []);

        return is_array($roles) ? array_values($roles) : ['member'];
    }

    /**
     * @return list<string>
     */
    public function adminRoles(): array
    {
        $roles = config('rbac.roles.admin', []);

        return is_array($roles) ? array_values($roles) : [];
    }

    public function roleForUser(User $user): ?string
    {
        /** @var string|null $role */
        $role = $this->users()
            ->whereKey($user->getKey())
            ->value('team_user.role');

        return $role;
    }

    public function wouldRemoveLastAdmin(User $user, ?string $newRole = null): bool
    {
        $currentRole = $this->roleForUser($user);

        if ($currentRole === null) {
            return false;
        }

        $adminRoles = $this->adminRoles();
        $isCurrentAdmin = in_array($currentRole, $adminRoles, true);
        $isStillAdmin = $newRole !== null && in_array($newRole, $adminRoles, true);

        if (! $isCurrentAdmin || $isStillAdmin) {
            return false;
        }

        return $this->users()
            ->wherePivotIn('role', $adminRoles)
            ->count() <= 1;
    }
}
