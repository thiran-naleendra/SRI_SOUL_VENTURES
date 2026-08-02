<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesPublicCache;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExperienceCategory extends DomainModel
{
    use InvalidatesPublicCache, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class)->orderBy('display_order');
    }
}
