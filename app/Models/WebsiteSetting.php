<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesPublicCache;
use App\Support\PublicCache;
use Illuminate\Support\Facades\Cache;

class WebsiteSetting extends DomainModel
{
    use InvalidatesPublicCache;

    public static function current(): ?self
    {
        $attributes = Cache::rememberForever(PublicCache::SETTINGS, fn () => static::query()->first()?->toArray());

        if ($attributes !== null && ! is_array($attributes)) {
            Cache::forget(PublicCache::SETTINGS);
            $attributes = static::query()->first()?->toArray();
            Cache::forever(PublicCache::SETTINGS, $attributes);
        }

        return $attributes ? (new static)->newFromBuilder($attributes) : null;
    }
}
