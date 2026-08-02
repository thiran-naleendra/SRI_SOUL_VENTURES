<?php

namespace App\Policies;

use App\Models\TeamMember;
use App\Models\User;

class TeamMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('team.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('team.manage');
    }

    public function update(User $user, TeamMember $item): bool
    {
        return $user->can('team.manage');
    }

    public function delete(User $user, TeamMember $item): bool
    {
        return $user->can('team.manage');
    }

    public function restore(User $user, TeamMember $item): bool
    {
        return $user->can('team.manage');
    }
}
