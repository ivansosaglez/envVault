<?php

namespace Database\Factories;

use App\Models\Environment;
use App\Models\EnvironmentVariable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnvironmentVariable>
 */
class EnvironmentVariableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'environment_id' => Environment::factory(),
            'key' => strtoupper(fake()->unique()->lexify('????_????')),
            'value' => fake()->word(),
            'is_secret' => false,
        ];
    }

    public function secret(): static
    {
        return $this->state(fn () => ['is_secret' => true]);
    }
}
