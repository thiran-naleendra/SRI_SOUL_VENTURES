<?php

namespace App\Policies;

use App\Models\Destination;
use App\Models\User;

class DestinationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('destinations.view');
    }

    public function view(User $user, Destination $destination): bool
    {
        return $user->can('destinations.view');
    }

    public function create(User $user): bool
    {
        return $user->can('destinations.create');
    }

    public function update(User $user, Destination $destination): bool
    {
        return $user->can('destinations.update');
    }

    public function delete(User $user, Destination $destination): bool
    {
        return $user->can('destinations.delete');
    }

    public function restore(User $user, Destination $destination): bool
    {
        return $user->can('destinations.delete');
    }
}
