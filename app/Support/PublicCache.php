<?php

namespace App\Support;

use App\Models\Destination;
use App\Models\DestinationRegion;
use App\Models\ExperienceCategory;
use App\Models\Package;
use App\Models\TravelStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class PublicCache
{
    public const SETTINGS = 'public.website-settings.v2';

    public const SITEMAP = 'public.sitemap';

    private const CATEGORIES = 'public.references.experience-categories.v3';

    private const DESTINATIONS = 'public.references.destinations.v3';

    private const REGIONS = 'public.references.destination-regions.v3';

    private const PACKAGES = 'public.references.packages.v3';

    private const TRAVEL_STYLES = 'public.references.travel-styles.v3';

    public static function experienceCategories(): Collection
    {
        $rows = self::rememberRows(self::CATEGORIES, fn () => ExperienceCategory::query()
            ->where('is_active', true)->orderBy('display_order')->orderBy('name')->get(['id', 'name', 'slug'])->toArray());

        return ExperienceCategory::hydrate($rows);
    }

    public static function destinations(): Collection
    {
        $rows = self::rememberRows(self::DESTINATIONS, fn () => Destination::query()
            ->where('is_active', true)->orderBy('display_order')->orderBy('name')->get(['id', 'name', 'slug'])->toArray());

        return Destination::hydrate($rows);
    }

    public static function destinationRegions(): Collection
    {
        $rows = self::rememberRows(self::REGIONS, fn () => DestinationRegion::query()
            ->where('is_active', true)->orderBy('display_order')->orderBy('name')->get(['id', 'name', 'slug'])->toArray());

        return DestinationRegion::hydrate($rows);
    }

    public static function travelStyles(): Collection
    {
        $rows = self::rememberRows(self::TRAVEL_STYLES, fn () => TravelStyle::query()
            ->where('is_active', true)->orderBy('display_order')->orderBy('name')->get(['id', 'name', 'slug', 'icon'])->toArray());

        return TravelStyle::hydrate($rows);
    }

    public static function packages(): Collection
    {
        $rows = self::rememberRows(self::PACKAGES, fn () => Package::query()
            ->where('is_active', true)->orderBy('title')->get(['id', 'title', 'slug'])->toArray());

        return Package::hydrate($rows);
    }

    public static function forgetSettings(): void
    {
        Cache::forget(self::SETTINGS);
    }

    public static function forgetSitemap(): void
    {
        Cache::forget(self::SITEMAP);
    }

    public static function forgetDestinations(): void
    {
        Cache::forget(self::DESTINATIONS);
    }

    public static function forgetRegions(): void
    {
        Cache::forget(self::REGIONS);
    }

    public static function forgetCategories(): void
    {
        Cache::forget(self::CATEGORIES);
    }

    public static function forgetTravelStyles(): void
    {
        Cache::forget(self::TRAVEL_STYLES);
    }

    public static function forgetPackages(): void
    {
        Cache::forget(self::PACKAGES);
    }

    private static function rememberRows(string $key, callable $query): array
    {
        $rows = Cache::remember($key, now()->addHours(12), $query);

        if (! is_array($rows)) {
            Cache::forget($key);
            $rows = $query();
            Cache::put($key, $rows, now()->addHours(12));
        }

        return $rows;
    }
}
