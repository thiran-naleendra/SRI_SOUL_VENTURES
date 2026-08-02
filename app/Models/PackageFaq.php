<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageFaq extends DomainModel
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
