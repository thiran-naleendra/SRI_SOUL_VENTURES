<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\Package;
use App\Models\PageSection;
use App\Support\PublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Destination::query()
            ->with('region')
            ->where('is_active', true)
            ->when($request->string('search')->trim()->value(), fn (Builder $query, string $search) => $query->where(
                fn (Builder $nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('full_description', 'like', "%{$search}%")
                    ->orWhere('best_time_to_visit', 'like', "%{$search}%")
                    ->orWhereHas('region', fn (Builder $region) => $region->where('name', 'like', "%{$search}%"))
            ))
            ->when($request->integer('region'), fn (Builder $query, int $region) => $query->where('destination_region_id', $region))
            ->orderBy('display_order')
            ->orderBy('name');

        $mapDestinations = (clone $query)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('public.destinations.index', [
            'destinations' => $query->paginate(12)->withQueryString(),
            'regions' => PublicCache::destinationRegions(),
            'mapDestinations' => $mapDestinations,
            'sections' => PageSection::where('page_key', 'destinations')->where('is_active', true)->get()->keyBy('section_key'),
        ]);
    }

    public function show(Destination $destination): View
    {
        abort_unless($destination->is_active, 404);

        $destination->load(['region', 'images', 'attractions', 'activities', 'travelTips']);
        $relatedExperiences = Experience::query()
            ->with(['category', 'destination', 'travelStyles'])
            ->where('is_active', true)
            ->where('destination_id', $destination->id)
            ->orderByDesc('is_popular')
            ->orderBy('display_order')
            ->limit(3)
            ->get();
        $relatedPackages = Package::query()
            ->with(['category', 'destinations', 'travelStyles'])
            ->where('is_active', true)
            ->whereHas('destinations', fn (Builder $query) => $query->where('destinations.id', $destination->id))
            ->orderByDesc('is_popular')
            ->orderBy('display_order')
            ->limit(3)
            ->get();

        return view('public.destinations.show', [
            'destination' => $destination,
            'relatedExperiences' => $relatedExperiences,
            'relatedPackages' => $relatedPackages,
            'sections' => PageSection::where('page_key', 'destinations')->where('is_active', true)->get()->keyBy('section_key'),
        ]);
    }
}
