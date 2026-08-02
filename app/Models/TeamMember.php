<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends DomainModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
