<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('faqs.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('faqs.manage');
    }

    public function update(User $user, Faq $item): bool
    {
        return $user->can('faqs.manage');
    }

    public function delete(User $user, Faq $item): bool
    {
        return $user->can('faqs.manage');
    }

    public function restore(User $user, Faq $item): bool
    {
        return $user->can('faqs.manage');
    }
}
