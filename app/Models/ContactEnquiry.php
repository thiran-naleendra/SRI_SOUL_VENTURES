<?php

namespace App\Models;

class ContactEnquiry extends DomainModel
{
    protected function casts(): array
    {
        return ['is_read' => 'boolean', 'replied_at' => 'datetime'];
    }
}
