<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            $project->slug = static::uniqueSlugFor($project->user_id, $project->slug ?: $project->name);
        });
    }

    /**
     * Build a slug that is unique among the projects of the given user.
     */
    public static function uniqueSlugFor(int $userId, string $source): string
    {
        $base = Str::slug($source) ?: 'project';
        $slug = $base;
        $suffix = 2;

        while (static::where('user_id', $userId)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function scopeOwnedBy(Builder $query, User $user): void
    {
        $query->where('user_id', $user->getKey());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function environments(): HasMany
    {
        return $this->hasMany(Environment::class)->orderBy('id');
    }

    public function variables(): HasManyThrough
    {
        return $this->hasManyThrough(EnvironmentVariable::class, Environment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest('created_at')->latest('id');
    }
}
