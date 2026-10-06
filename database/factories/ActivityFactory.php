<?php

namespace Database\Factories;

use App\Enums\ActivityAction;
use App\Models\Activity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'action' => ActivityAction::VariableAdded,
            'subject' => strtoupper(fake()->lexify('????_????')),
        ];
    }
}
