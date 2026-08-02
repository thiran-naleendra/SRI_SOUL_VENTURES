<?php

namespace App\Models;

class PageSection extends DomainModel
{
    protected function casts(): array
    {
        return ['settings' => 'array', 'is_active' => 'boolean'];
    }
}
