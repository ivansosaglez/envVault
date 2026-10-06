<?php

namespace App\Models;

use Database\Factories\EnvironmentVariableFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The `value` column is always stored encrypted (Laravel's `encrypted` cast,
 * AES-256-CBC keyed with APP_KEY). `is_secret` only decides whether the UI and
 * the API mask the value.
 */
#[Fillable(['key', 'value', 'is_secret'])]
#[Hidden(['value'])]
class EnvironmentVariable extends Model
{
    /** @use HasFactory<EnvironmentVariableFactory> */
    use HasFactory;

    public const MASK = '••••••••••••••';

    public const KEY_PATTERN = '/^[A-Za-z_][A-Za-z0-9_.]*$/';

    /**
     * Heuristic used when a variable is created without an explicit choice (imports, API).
     */
    public static function looksSecret(string $key): bool
    {
        return (bool) preg_match('/(SECRET|PASSWORD|PASSWD|TOKEN|PRIVATE|CREDENTIAL|API_?KEY|APP_KEY|DSN|_KEY$)/i', $key);
    }

    protected function casts(): array
    {
        return [
            'value' => 'encrypted',
            'is_secret' => 'boolean',
        ];
    }

    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term !== '') {
            $escaped = addcslashes($term, '%_\\');
            $query->where('key', 'ilike', "%{$escaped}%");
        }
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function hasValue(): bool
    {
        return ($this->value ?? '') !== '';
    }

    /**
     * The value that is safe to render by default: masked when secret.
     */
    public function displayValue(): string
    {
        if ($this->is_secret) {
            return $this->hasValue() ? self::MASK : '';
        }

        return (string) $this->value;
    }
}
