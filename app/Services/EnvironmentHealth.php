<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\HealthReport;
use App\Models\Environment;
use App\Models\EnvironmentVariable;
use Illuminate\Support\Collection;

/**
 * Scores an environment against the set of variables the project expects,
 * which is the union of the keys defined across all its environments.
 */
final class EnvironmentHealth
{
    /**
     * @param  iterable<string>  $expectedKeys
     */
    public function check(Environment $environment, iterable $expectedKeys): HealthReport
    {
        $expected = collect($expectedKeys)->unique()->values();
        $variables = $environment->variables->keyBy('key');

        [$missing, $present] = $expected->partition(fn (string $key) => ! $variables->has($key));

        $empty = $present
            ->filter(fn (string $key) => ! $variables[$key]->hasValue())
            ->values();

        return new HealthReport(
            expected: $expected->count(),
            configured: $present->count() - $empty->count(),
            missing: $missing->values()->all(),
            empty: $empty->all(),
        );
    }

    /**
     * Union of the keys defined in the given environments.
     *
     * @param  Collection<int, Environment>  $environments
     * @return list<string>
     */
    public function expectedKeys(Collection $environments): array
    {
        return $environments
            ->flatMap(fn (Environment $environment) => $environment->variables->map(fn (EnvironmentVariable $variable) => $variable->key))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
