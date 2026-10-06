<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ValidationReport
{
    /**
     * @param  list<string>  $missing  Expected by the environment, absent from the pasted .env.
     * @param  list<string>  $empty  Present in the pasted .env but with an empty value.
     * @param  list<string>  $present  Present and filled in.
     * @param  list<string>  $unknown  In the pasted .env but not defined in the environment.
     */
    public function __construct(
        public array $missing,
        public array $empty,
        public array $present,
        public array $unknown,
        public ParsedEnv $parsed,
    ) {}

    public function isValid(): bool
    {
        return $this->missing === [] && $this->empty === [];
    }
}
