<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\VariableStatus;

final readonly class ComparisonResult
{
    /**
     * @param  list<ComparisonRow>  $rows  Sorted by key.
     */
    public function __construct(public array $rows) {}

    public function total(): int
    {
        return count($this->rows);
    }

    public function count(VariableStatus $status): int
    {
        return count($this->only($status));
    }

    /**
     * @return list<ComparisonRow>
     */
    public function only(VariableStatus ...$statuses): array
    {
        return array_values(array_filter(
            $this->rows,
            fn (ComparisonRow $row) => in_array($row->status, $statuses, true),
        ));
    }

    public function inSync(): bool
    {
        return $this->count(VariableStatus::Same) === $this->total();
    }
}
