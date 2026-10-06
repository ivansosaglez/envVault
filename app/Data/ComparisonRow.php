<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\VariableStatus;
use App\Models\EnvironmentVariable;

final readonly class ComparisonRow
{
    public function __construct(
        public string $key,
        public VariableStatus $status,
        public ?EnvironmentVariable $left,
        public ?EnvironmentVariable $right,
    ) {}

    public function isSecret(): bool
    {
        return ($this->left?->is_secret ?? false) || ($this->right?->is_secret ?? false);
    }
}
