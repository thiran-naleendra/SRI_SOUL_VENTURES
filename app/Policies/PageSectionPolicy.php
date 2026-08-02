<?php

namespace App\Policies;

use App\Models\PageSection;
use App\Models\User;

class PageSectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('pages.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('pages.manage');
    }

    public function update(User $user, PageSection $section): bool
    {
        return $user->can('pages.manage');
    }

    public function delete(User $user, PageSection $section): bool
    {
        return $user->can('pages.manage');
    }
}
