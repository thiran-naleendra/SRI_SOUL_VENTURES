<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesPublicCache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends DomainModel
{
    use InvalidatesPublicCache, SoftDeletes;

    protected function casts(): array
    {
        return ['starting_price' => 'decimal:2', 'discount_price' => 'decimal:2', 'is_featured' => 'boolean', 'is_popular' => 'boolean', 'is_customizable' => 'boolean', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PackageCategory::class, 'package_category_id');
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'package_destination');
    }

    public function travelStyles(): BelongsToMany
    {
        return $this->belongsToMany(TravelStyle::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PackageImage::class)->orderBy('display_order');
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(PackageItinerary::class)->orderBy('display_order')->orderBy('day_number');
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(PackageHighlight::class)->orderBy('display_order');
    }

    public function inclusions(): HasMany
    {
        return $this->hasMany(PackageInclusion::class)->orderBy('display_order');
    }

    public function exclusions(): HasMany
    {
        return $this->hasMany(PackageExclusion::class)->orderBy('display_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(PackageFaq::class)->orderBy('display_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PackageReview::class)->orderBy('display_order');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(PackageEnquiry::class)->latest();
    }

    public function customTourRequests(): HasMany
    {
        return $this->hasMany(CustomTourRequest::class)->latest();
    }
}
