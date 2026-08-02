<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationAttraction extends DomainModel
{
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
