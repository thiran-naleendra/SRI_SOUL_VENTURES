<?php

namespace App\Policies;

use App\Models\Experience;
use App\Models\User;

class ExperiencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('experiences.view');
    }

    public function view(User $user, Experience $experience): bool
    {
        return $user->can('experiences.view');
    }

    public function create(User $user): bool
    {
        return $user->can('experiences.create');
    }

    public function update(User $user, Experience $experience): bool
    {
        return $user->can('experiences.update');
    }

    public function delete(User $user, Experience $experience): bool
    {
        return $user->can('experiences.delete');
    }

    public function restore(User $user, Experience $experience): bool
    {
        return $user->can('experiences.delete');
    }
}
