<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageCategory extends DomainModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class)->orderBy('display_order');
    }
}
