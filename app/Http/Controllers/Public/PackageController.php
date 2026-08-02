<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PageSection;
use App\Models\WebsiteSetting;
use App\Support\PublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        $query = Package::query()
            ->with(['category', 'destinations', 'travelStyles'])
            ->where('is_active', true)
            ->when($request->string('search')->trim()->value(), fn (Builder $query, string $search) => $query->where(
                fn (Builder $nested) => $nested
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('full_description', 'like', "%{$search}%")
                    ->orWhereHas('destinations', fn (Builder $destination) => $destination->where('name', 'like', "%{$search}%"))
            ))
            ->when($request->string('duration')->value(), fn (Builder $query, string $duration) => $this->applyDurationFilter($query, $duration))
            ->when($request->string('travel_style')->value(), fn (Builder $query, string $style) => $query->whereHas(
                'travelStyles',
                fn (Builder $styles) => $styles
                    ->where('travel_styles.is_active', true)
                    ->where(fn (Builder $match) => $match->where('travel_styles.slug', $style)->when(is_numeric($style), fn (Builder $numeric) => $numeric->orWhere('travel_styles.id', (int) $style)))
            ))
            ->when($request->integer('destination'), fn (Builder $query, int $destination) => $query->whereHas('destinations', fn (Builder $destinations) => $destinations->where('destinations.id', $destination)->where('destinations.is_active', true)))
            ->when($request->filled('budget_min') && is_numeric($request->input('budget_min')), fn (Builder $query) => $query->where('starting_price', '>=', (float) $request->input('budget_min')))
            ->when($request->filled('budget_max') && is_numeric($request->input('budget_max')), fn (Builder $query) => $query->where('starting_price', '<=', (float) $request->input('budget_max')))
            ->when($request->integer('travelers'), fn (Builder $query, int $travelers) => $query
                ->where('minimum_travelers', '<=', $travelers)
                ->where(fn (Builder $maximum) => $maximum->whereNull('maximum_travelers')->orWhere('maximum_travelers', '>=', $travelers)));

        $this->applySort($query, $request->string('sort')->value());

        return view('public.packages.index', [
            'packages' => $query->paginate(12)->withQueryString(),
            'destinations' => PublicCache::destinations(),
            'travelStyles' => PublicCache::travelStyles(),
            'sections' => PageSection::where('page_key', 'packages')->where('is_active', true)->get()->keyBy('section_key'),
        ]);
    }

    public function show(Package $package): View
    {
        abort_unless($package->is_active, 404);

        $package->load([
            'category',
            'destinations' => fn ($query) => $query->where('destinations.is_active', true),
            'travelStyles' => fn ($query) => $query->where('travel_styles.is_active', true),
            'images',
            'itineraries',
            'highlights',
            'inclusions',
            'exclusions',
            'faqs' => fn ($query) => $query->where('is_active', true),
            'reviews' => fn ($query) => $query->where('is_approved', true),
        ]);

        $destinationIds = $package->destinations->modelKeys();
        $relatedPackages = Package::query()
            ->with(['category', 'destinations', 'travelStyles'])
            ->where('is_active', true)
            ->whereKeyNot($package->getKey())
            ->where(fn (Builder $query) => $query
                ->where('package_category_id', $package->package_category_id)
                ->when($destinationIds, fn (Builder $related) => $related->orWhereHas('destinations', fn (Builder $destinations) => $destinations->whereKey($destinationIds))))
            ->orderByDesc('is_popular')
            ->orderBy('display_order')
            ->limit(3)
            ->get();

        return view('public.packages.show', [
            'package' => $package,
            'relatedPackages' => $relatedPackages,
            'sections' => PageSection::where('page_key', 'packages')->where('is_active', true)->get()->keyBy('section_key'),
            'websiteSettings' => WebsiteSetting::current(),
        ]);
    }

    private function applyDurationFilter(Builder $query, string $duration): Builder
    {
        return match ($duration) {
            'short' => $query->whereBetween('days', [1, 3]),
            'week' => $query->whereBetween('days', [4, 7]),
            'two_weeks' => $query->whereBetween('days', [8, 14]),
            'extended' => $query->where('days', '>=', 15),
            default => $query,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'popular' => $query->orderByDesc('is_popular')->orderByDesc('is_featured')->orderBy('display_order'),
            'newest' => $query->latest(),
            'price_asc' => $query->orderByRaw('starting_price IS NULL')->orderBy('starting_price'),
            'price_desc' => $query->orderByDesc('starting_price'),
            'duration_asc' => $query->orderBy('days')->orderBy('nights'),
            default => $query->orderBy('display_order')->orderBy('title'),
        };
    }
}
