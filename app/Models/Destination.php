<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesPublicCache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Destination extends DomainModel
{
    use InvalidatesPublicCache, SoftDeletes;

    protected function casts(): array
    {
        return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'is_featured' => 'boolean', 'is_active' => 'boolean'];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(DestinationRegion::class, 'destination_region_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(DestinationImage::class)->orderBy('display_order');
    }

    public function attractions(): HasMany
    {
        return $this->hasMany(DestinationAttraction::class)->orderBy('display_order');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(DestinationActivity::class)->orderBy('display_order');
    }

    public function travelTips(): HasMany
    {
        return $this->hasMany(DestinationTravelTip::class)->orderBy('display_order');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class)->orderBy('display_order');
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_destination');
    }

    public function customTourRequests(): BelongsToMany
    {
        return $this->belongsToMany(CustomTourRequest::class);
    }
}
