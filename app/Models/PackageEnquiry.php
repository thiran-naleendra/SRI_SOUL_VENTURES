<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageEnquiry extends DomainModel
{
    protected function casts(): array
    {
        return ['preferred_start_date' => 'date', 'preferred_end_date' => 'date', 'contacted_at' => 'datetime'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
