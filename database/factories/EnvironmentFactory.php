<?php

namespace Database\Factories;

use App\Models\Environment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Environment>
 */
class EnvironmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->unique()->word(),
            'color' => fake()->randomElement(['emerald', 'amber', 'rose', 'sky', null]),
        ];
    }
}
