<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\PageSection;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\WebsiteSetting;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('public.pages.about', [
            'sections' => $this->sections('about'),
            'team' => TeamMember::where('is_active', true)->orderBy('display_order')->get(),
            'testimonials' => Testimonial::where('is_active', true)->orderByDesc('is_featured')->orderBy('display_order')->limit(6)->get(),
            'websiteSettings' => WebsiteSetting::current(),
        ]);
    }

    public function contact(): View
    {
        return view('public.pages.contact', [
            'sections' => $this->sections('contact'),
            'faqs' => Faq::where('is_active', true)->orderBy('display_order')->limit(12)->get(),
            'websiteSettings' => WebsiteSetting::current(),
        ]);
    }

    private function sections(string $page): Collection
    {
        return PageSection::where('page_key', $page)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->keyBy('section_key');
    }
}
