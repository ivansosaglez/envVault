<?php

namespace App\Policies;

use App\Models\EnvironmentVariable;
use App\Models\User;

class EnvironmentVariablePolicy
{
    public function view(User $user, EnvironmentVariable $variable): bool
    {
        return $variable->environment->project->user_id === $user->id;
    }

    public function update(User $user, EnvironmentVariable $variable): bool
    {
        return $this->view($user, $variable);
    }

    public function delete(User $user, EnvironmentVariable $variable): bool
    {
        return $this->view($user, $variable);
    }

    public function reveal(User $user, EnvironmentVariable $variable): bool
    {
        return $this->view($user, $variable);
    }
}
