<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ParsedEnv
{
    /**
     * @param  array<string, string>  $variables  Key => value. When a key repeats, the last one wins.
     * @param  list<string>  $duplicates  Keys that were defined more than once.
     * @param  list<int>  $invalidLines  1-based numbers of lines that could not be parsed.
     */
    public function __construct(
        public array $variables = [],
        public array $duplicates = [],
        public array $invalidLines = [],
    ) {}

    public function count(): int
    {
        return count($this->variables);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->variables);
    }
}
