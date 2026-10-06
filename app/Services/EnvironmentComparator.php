<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ComparisonResult;
use App\Data\ComparisonRow;
use App\Enums\VariableStatus;
use App\Models\EnvironmentVariable;

/**
 * Compares the variables of two environments, "left" being the reference:
 *
 *  - Same:      key in both, equal value
 *  - Different: key in both, different value
 *  - Missing:   key only in left (missing in right)
 *  - Extra:     key only in right
 *
 * Values are compared in memory and are never copied into the result, so a
 * secret can be reported as "different" without being revealed.
 */
final class EnvironmentComparator
{
    /**
     * @param  iterable<EnvironmentVariable>  $left
     * @param  iterable<EnvironmentVariable>  $right
     */
    public function compare(iterable $left, iterable $right): ComparisonResult
    {
        $left = $this->indexByKey($left);
        $right = $this->indexByKey($right);

        $keys = array_unique([...array_keys($left), ...array_keys($right)]);
        sort($keys, SORT_STRING);

        $rows = array_map(
            fn (string $key) => new ComparisonRow($key, $this->statusFor($left[$key] ?? null, $right[$key] ?? null), $left[$key] ?? null, $right[$key] ?? null),
            $keys,
        );

        return new ComparisonResult($rows);
    }

    private function statusFor(?EnvironmentVariable $left, ?EnvironmentVariable $right): VariableStatus
    {
        return match (true) {
            $right === null => VariableStatus::Missing,
            $left === null => VariableStatus::Extra,
            hash_equals((string) $left->value, (string) $right->value) => VariableStatus::Same,
            default => VariableStatus::Different,
        };
    }

    /**
     * @param  iterable<EnvironmentVariable>  $variables
     * @return array<string, EnvironmentVariable>
     */
    private function indexByKey(iterable $variables): array
    {
        $indexed = [];

        foreach ($variables as $variable) {
            $indexed[$variable->key] = $variable;
        }

        return $indexed;
    }
}
