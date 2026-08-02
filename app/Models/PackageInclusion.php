<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageInclusion extends DomainModel
{
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
