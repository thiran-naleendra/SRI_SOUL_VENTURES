<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\PageSection;
use App\Models\WebsiteSetting;
use App\Support\PublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Experience::query()
            ->with(['category', 'destination', 'travelStyles'])
            ->where('is_active', true)
            ->when($request->string('search')->trim()->value(), fn (Builder $query, string $search) => $query->where(
                fn (Builder $nested) => $nested
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('full_description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('destination', fn (Builder $destination) => $destination->where('name', 'like', "%{$search}%"))
            ))
            ->when($request->integer('category'), fn (Builder $query, int $id) => $query->where('experience_category_id', $id))
            ->when($request->integer('destination'), fn (Builder $query, int $id) => $query->where('destination_id', $id))
            ->when($request->string('travel_style')->value(), fn (Builder $query, string $style) => $query->whereHas(
                'travelStyles',
                fn (Builder $styles) => $styles
                    ->where('travel_styles.is_active', true)
                    ->where(fn (Builder $match) => $match->where('travel_styles.slug', $style)->when(is_numeric($style), fn (Builder $numeric) => $numeric->orWhere('travel_styles.id', (int) $style)))
            ))
            ->when($request->string('duration')->value(), fn (Builder $query, string $duration) => $this->applyDurationFilter($query, $duration))
            ->when($request->filled('price_min') && is_numeric($request->input('price_min')), fn (Builder $query) => $query->where('starting_price', '>=', (float) $request->input('price_min')))
            ->when($request->filled('price_max') && is_numeric($request->input('price_max')), fn (Builder $query) => $query->where('starting_price', '<=', (float) $request->input('price_max')));

        $this->applySort($query, $request->string('sort')->value());

        return view('public.experiences.index', [
            'experiences' => $query->paginate(12)->withQueryString(),
            'categories' => PublicCache::experienceCategories(),
            'destinations' => PublicCache::destinations(),
            'travelStyles' => PublicCache::travelStyles(),
            'hero' => PageSection::where(['page_key' => 'experiences', 'section_key' => 'hero', 'is_active' => true])->first(),
        ]);
    }

    public function show(Experience $experience): View
    {
        abort_unless($experience->is_active, 404);

        $experience->load(['category', 'destination', 'travelStyles', 'images', 'highlights', 'inclusions', 'exclusions']);
        $relatedExperiences = Experience::query()
            ->with(['category', 'destination', 'travelStyles'])
            ->where('is_active', true)
            ->whereKeyNot($experience->getKey())
            ->where(fn (Builder $query) => $query
                ->where('experience_category_id', $experience->experience_category_id)
                ->orWhere('destination_id', $experience->destination_id))
            ->orderByDesc('is_popular')
            ->orderBy('display_order')
            ->limit(3)
            ->get();

        return view('public.experiences.show', [
            'experience' => $experience,
            'relatedExperiences' => $relatedExperiences,
            'sections' => PageSection::where('page_key', 'experiences')->where('is_active', true)->get()->keyBy('section_key'),
            'websiteSettings' => WebsiteSetting::current(),
        ]);
    }

    private function applyDurationFilter(Builder $query, string $duration): Builder
    {
        return match ($duration) {
            'under_4_hours' => $query->whereRaw("LOWER(duration_unit) IN ('hour', 'hours', 'hr', 'hrs')")->where('duration_value', '<', 4),
            'half_day' => $query->whereRaw("LOWER(duration_unit) IN ('hour', 'hours', 'hr', 'hrs')")->whereBetween('duration_value', [4, 8]),
            'full_day' => $query->where(fn (Builder $duration) => $duration
                ->where(fn (Builder $hours) => $hours->whereRaw("LOWER(duration_unit) IN ('hour', 'hours', 'hr', 'hrs')")->whereBetween('duration_value', [9, 24]))
                ->orWhere(fn (Builder $days) => $days->whereRaw("LOWER(duration_unit) IN ('day', 'days')")->where('duration_value', 1))),
            'multi_day' => $query->where(fn (Builder $duration) => $duration
                ->where(fn (Builder $days) => $days->whereRaw("LOWER(duration_unit) IN ('day', 'days')")->where('duration_value', '>', 1))
                ->orWhere(fn (Builder $hours) => $hours->whereRaw("LOWER(duration_unit) IN ('hour', 'hours', 'hr', 'hrs')")->where('duration_value', '>', 24))),
            default => $query,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'popular' => $query->orderByDesc('is_popular')->orderBy('display_order'),
            'newest' => $query->latest(),
            'price_asc' => $query->orderByRaw('starting_price IS NULL')->orderBy('starting_price'),
            'price_desc' => $query->orderByDesc('starting_price'),
            default => $query->orderBy('display_order')->orderBy('title'),
        };
    }
}
