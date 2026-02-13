<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
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
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (Project $project): void {
            if ($project->isDirty('name') || blank($project->slug)) {
                $project->slug = static::generateUniqueSlug($project->name, $project->getKey());
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsToMany<Team, $this>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /**
     * @return HasMany<ProjectVulnerability, $this>
     */
    public function vulnerabilities(): HasMany
    {
        return $this->hasMany(ProjectVulnerability::class);
    }

    /**
     * @return HasMany<ProjectWebhookToken, $this>
     */
    public function webhookTokens(): HasMany
    {
        return $this->hasMany(ProjectWebhookToken::class);
    }

    protected static function generateUniqueSlug(string $name, string|int|null $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'project';
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
}
