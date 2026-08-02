<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\Package;
use App\Models\PageSection;
use App\Models\Testimonial;
use App\Support\PublicCache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('public.home', [
            'sections' => PageSection::query()
                ->where('page_key', 'home')
                ->where('is_active', true)
                ->orderBy('display_order')
                ->get()
                ->keyBy('section_key'),
            'travelStyles' => PublicCache::travelStyles(),
            'experiences' => Experience::query()
                ->with(['category', 'destination', 'travelStyles'])
                ->where('is_active', true)
                ->where('is_popular', true)
                ->orderBy('display_order')
                ->limit(6)
                ->get(),
            'packages' => Package::query()
                ->with(['category', 'destinations'])
                ->where('is_active', true)
                ->where(fn ($query) => $query->where('is_featured', true)->orWhere('is_popular', true))
                ->orderByDesc('is_featured')
                ->orderBy('display_order')
                ->limit(6)
                ->get(),
            'destinations' => Destination::query()
                ->with('region')
                ->where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('display_order')
                ->limit(6)
                ->get(),
            'testimonials' => Testimonial::query()
                ->where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('display_order')
                ->limit(6)
                ->get(),
        ]);
    }
}
