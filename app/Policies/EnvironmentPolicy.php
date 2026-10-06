<?php

namespace App\Policies;

use App\Models\Environment;
use App\Models\User;

class EnvironmentPolicy
{
    public function view(User $user, Environment $environment): bool
    {
        return $environment->project->user_id === $user->id;
    }

    public function update(User $user, Environment $environment): bool
    {
        return $this->view($user, $environment);
    }

    public function delete(User $user, Environment $environment): bool
    {
        return $this->view($user, $environment);
    }
}
