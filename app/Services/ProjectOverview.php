<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Environment;
use Illuminate\Support\Collection;

/**
 * Project-wide sync summary. Every distinct key lands in exactly one bucket:
 *
 *  - missing:      absent from at least one environment
 *  - different:    present everywhere, but with different values
 *  - synchronized: present everywhere with the same value
 */
final class ProjectOverview
{
    /**
     * @param  Collection<int, Environment>  $environments  With `variables` loaded.
     * @return array{total: int, synchronized: int, missing: int, different: int}
     */
    public function summarize(Collection $environments): array
    {
        $byKey = [];

        foreach ($environments as $environment) {
            foreach ($environment->variables as $variable) {
                $byKey[$variable->key][$environment->id] = $variable->value;
            }
        }

        $summary = ['total' => count($byKey), 'synchronized' => 0, 'missing' => 0, 'different' => 0];

        foreach ($byKey as $values) {
            $summary[match (true) {
                count($values) < $environments->count() => 'missing',
                count(array_unique($values)) > 1 => 'different',
                default => 'synchronized',
            }]++;
        }

        return $summary;
    }
}
