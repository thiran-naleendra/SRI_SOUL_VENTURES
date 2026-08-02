<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('testimonials.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('testimonials.manage');
    }

    public function update(User $user, Testimonial $item): bool
    {
        return $user->can('testimonials.manage');
    }

    public function delete(User $user, Testimonial $item): bool
    {
        return $user->can('testimonials.manage');
    }

    public function restore(User $user, Testimonial $item): bool
    {
        return $user->can('testimonials.manage');
    }
}
