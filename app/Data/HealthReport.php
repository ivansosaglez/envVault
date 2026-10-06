<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\HealthStatus;

final readonly class HealthReport
{
    /**
     * @param  list<string>  $missing  Expected keys that do not exist in the environment.
     * @param  list<string>  $empty  Keys that exist but have no value.
     */
    public function __construct(
        public int $expected,
        public int $configured,
        public array $missing,
        public array $empty,
    ) {}

    public function percentage(): int
    {
        return $this->expected === 0 ? 100 : (int) floor($this->configured / $this->expected * 100);
    }

    public function status(): HealthStatus
    {
        return match (true) {
            $this->missing === [] && $this->empty === [] => HealthStatus::Healthy,
            $this->percentage() >= 80 => HealthStatus::Attention,
            default => HealthStatus::Incomplete,
        };
    }
}
