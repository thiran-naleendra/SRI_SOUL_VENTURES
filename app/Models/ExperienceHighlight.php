<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceHighlight extends DomainModel
{
    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }
}
