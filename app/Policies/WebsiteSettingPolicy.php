<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebsiteSetting;

class WebsiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function update(User $user, WebsiteSetting $setting): bool
    {
        return $user->can('settings.manage');
    }
}
