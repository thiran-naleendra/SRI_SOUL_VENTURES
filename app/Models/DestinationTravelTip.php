<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationTravelTip extends DomainModel
{
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
