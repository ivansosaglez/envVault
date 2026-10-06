<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ImportPlan
{
    /**
     * @param  list<string>  $new  Keys that do not exist yet in the environment.
     * @param  list<string>  $conflicts  Keys that already exist and would be overwritten.
     * @param  list<string>  $secretConflicts  The subset of conflicts that are secrets.
     */
    public function __construct(
        public array $new,
        public array $conflicts,
        public array $secretConflicts,
        public ParsedEnv $parsed,
    ) {}

    public function total(): int
    {
        return count($this->new) + count($this->conflicts);
    }
}
