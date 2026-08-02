<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends DomainModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_active' => 'boolean'];
    }
}
