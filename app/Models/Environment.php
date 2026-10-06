<?php

namespace App\Models;

use Database\Factories\EnvironmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'color'])]
class Environment extends Model
{
    /** @use HasFactory<EnvironmentFactory> */
    use HasFactory;

    public const COLORS = ['emerald', 'sky', 'amber', 'rose', 'violet', 'zinc'];

    protected static function booted(): void
    {
        static::creating(function (Environment $environment) {
            $environment->slug ??= Str::slug($environment->name);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function variables(): HasMany
    {
        return $this->hasMany(EnvironmentVariable::class)->orderBy('key');
    }
}
