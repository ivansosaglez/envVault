<?php

namespace App\Enums;

enum HealthStatus: string
{
    case Healthy = 'healthy';
    case Attention = 'attention';
    case Incomplete = 'incomplete';

    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'Healthy',
            self::Attention => 'Attention needed',
            self::Incomplete => 'Incomplete',
        };
    }
}
