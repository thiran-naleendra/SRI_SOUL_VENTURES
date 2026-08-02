<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesPublicCache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Experience extends DomainModel
{
    use InvalidatesPublicCache, SoftDeletes;

    protected function casts(): array
    {
        return ['starting_price' => 'decimal:2', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'is_featured' => 'boolean', 'is_popular' => 'boolean', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExperienceCategory::class, 'experience_category_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function travelStyles(): BelongsToMany
    {
        return $this->belongsToMany(TravelStyle::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ExperienceImage::class)->orderBy('display_order');
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(ExperienceHighlight::class)->orderBy('display_order');
    }

    public function inclusions(): HasMany
    {
        return $this->hasMany(ExperienceInclusion::class)->orderBy('display_order');
    }

    public function exclusions(): HasMany
    {
        return $this->hasMany(ExperienceExclusion::class)->orderBy('display_order');
    }
}
