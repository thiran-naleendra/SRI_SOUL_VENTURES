<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends DomainModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
