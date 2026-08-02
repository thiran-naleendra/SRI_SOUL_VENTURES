<?php

namespace App\Models\Concerns;

use App\Models\Destination;
use App\Models\DestinationRegion;
use App\Models\ExperienceCategory;
use App\Models\Package;
use App\Models\TravelStyle;
use App\Models\WebsiteSetting;
use App\Support\PublicCache;
use Illuminate\Database\Eloquent\SoftDeletes;

trait InvalidatesPublicCache
{
    public static function bootInvalidatesPublicCache(): void
    {
        $invalidate = function (): void {
            PublicCache::forgetSitemap();

            match (static::class) {
                WebsiteSetting::class => PublicCache::forgetSettings(),
                Destination::class => PublicCache::forgetDestinations(),
                DestinationRegion::class => PublicCache::forgetRegions(),
                ExperienceCategory::class => PublicCache::forgetCategories(),
                Package::class => PublicCache::forgetPackages(),
                TravelStyle::class => PublicCache::forgetTravelStyles(),
                default => null,
            };
        };

        static::saved($invalidate);
        static::deleted($invalidate);

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored($invalidate);
        }
    }
}
