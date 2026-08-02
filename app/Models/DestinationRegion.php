<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesPublicCache;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationRegion extends DomainModel
{
    use InvalidatesPublicCache, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class)->orderBy('display_order');
    }
}
