<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ValidationReport;
use App\Models\Environment;

/**
 * Checks a pasted .env against the variables defined in an environment.
 */
final class EnvValidator
{
    public function __construct(private EnvParser $parser) {}

    public function validate(string $content, Environment $environment): ValidationReport
    {
        $parsed = $this->parser->parse($content);
        $expected = $environment->variables->pluck('key')->sort()->values();

        $missing = $expected->reject(fn (string $key) => $parsed->has($key));
        [$empty, $present] = $expected
            ->filter(fn (string $key) => $parsed->has($key))
            ->partition(fn (string $key) => $parsed->variables[$key] === '');

        $unknown = collect(array_keys($parsed->variables))
            ->reject(fn (string $key) => $expected->contains($key))
            ->sort();

        return new ValidationReport(
            missing: $missing->values()->all(),
            empty: $empty->values()->all(),
            present: $present->values()->all(),
            unknown: $unknown->values()->all(),
            parsed: $parsed,
        );
    }
}
