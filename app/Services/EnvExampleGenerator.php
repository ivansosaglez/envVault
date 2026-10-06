<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Builds a .env.example from variable names only. Values are never read.
 *
 * Output is deterministic: keys are de-duplicated, sorted alphabetically and
 * grouped in blocks by prefix (APP_*, DB_*, ...), separated by a blank line.
 */
final class EnvExampleGenerator
{
    /**
     * @param  iterable<string>  $keys
     */
    public function generate(iterable $keys): string
    {
        $unique = [];
        foreach ($keys as $key) {
            $unique[$key] = true;
        }

        $keys = array_keys($unique);
        sort($keys, SORT_STRING);

        $groups = [];
        foreach ($keys as $key) {
            $groups[strtok($key, '_')][] = "{$key}=";
        }

        return $groups === [] ? '' : implode("\n\n", array_map(fn (array $lines) => implode("\n", $lines), $groups))."\n";
    }
}
