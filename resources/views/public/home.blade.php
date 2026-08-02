@extends('layouts.public')

@section('content')
    @php
        $hero = $sections->get('hero');
        $vibesSection = $sections->get('travel_vibes');
        $experienceSection = $sections->get('popular_experiences');
        $packageSection = $sections->get('popular_packages');
        $destinationSection = $sections->get('popular_destinations');
        $whySection = $sections->get('why_us');
        $statisticsSection = $sections->get('statistics');
        $testimonialSection = $sections->get('testimonials');
        $customCta = $sections->get('custom_journey_cta');
        $whatsappCta = $sections->get('whatsapp_cta');
        $heroImage = $hero?->image_path ? Storage::disk('public')->url($hero->image_path) : null;
        $whyItems = is_array($whySection?->settings['items'] ?? null) ? $whySection->settings['items'] : [];
        $statistics = is_array($statisticsSection?->settings['items'] ?? null) ? $statisticsSection->settings['items'] : [];
    @endphp

    <x-public.hero
        :title="$hero?->heading ?: 'Discover the soul of Sri Lanka'"
        :highlighted-text="$hero?->settings['highlighted_text'] ?? null"
        :subtitle="$hero?->content ?: $hero?->subheading"
        :image="$heroImage"
        :button-text="$hero?->button_text"
        :button-url="$hero?->button_url ?: route('custom-tours')"
        :secondary-button-text="$hero?->settings['secondary_button_text'] ?? null"
        :secondary-button-url="$hero?->settings['secondary_button_url'] ?? route('experiences.index')"
    />

    <section class="vibe-section section-cream" aria-labelledby="travel-vibes-heading">
        <div class="container">
            <div class="vibe-panel">
                <div class="row align-items-center g-4">
                    <div class="col-lg-4">
                        <p class="section-kicker mb-2">{{ $vibesSection?->subheading ?: 'Find your travel vibe' }}</p>
                        <h2 id="travel-vibes-heading" class="h2 mb-0">{{ $vibesSection?->heading ?: 'How do you want to feel?' }}</h2>
                    </div>
                    <div class="col-lg-8">
                        @if ($travelStyles->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                @foreach ($travelStyles as $style)
                                    <a class="vibe-chip" href="{{ route('experiences.index', ['travel_style' => $style->slug]) }}">
                                        <span aria-hidden="true">✦</span> {{ $style->name }}
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <x-public.empty-state title="Travel styles are coming soon" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space" aria-labelledby="popular-experiences-heading">
        <div class="container">
            <x-public.section-heading
                :eyebrow="$experienceSection?->subheading ?: 'Unforgettable moments'"
                :title="$experienceSection?->heading ?: 'Popular experiences'"
                :subtitle="$experienceSection?->content"
            />
            @if ($experiences->isNotEmpty())
                <div class="row g-4">
                    @foreach ($experiences as $experience)
                        <div class="col-md-6 col-lg-4"><x-public.experience-card :experience="$experience" lazy show-favourite /></div>
                    @endforeach
                </div>
            @else
                <x-public.empty-state title="Popular experiences are coming soon" text="Our local team is preparing memorable ways to explore Sri Lanka." />
            @endif
        </div>
    </section>

    <section class="section-space section-cream" aria-labelledby="popular-packages-heading">
        <div class="container">
            <x-public.section-heading
                :eyebrow="$packageSection?->subheading ?: 'Handpicked journeys'"
                :title="$packageSection?->heading ?: 'Popular Sri Lanka packages'"
                :subtitle="$packageSection?->content"
            />
            @if ($packages->isNotEmpty())
                <div class="row g-4">
                    @foreach ($packages as $package)
                        <div class="col-md-6 col-lg-4"><x-public.package-card :package="$package" lazy /></div>
                    @endforeach
                </div>
            @else
                <x-public.empty-state title="Curated packages are coming soon" text="Tell us your ideas and we can create a custom journey now." />
            @endif
        </div>
    </section>

    <section class="section-space" aria-labelledby="popular-destinations-heading">
        <div class="container">
            <x-public.section-heading
                :eyebrow="$destinationSection?->subheading ?: 'Places worth knowing'"
                :title="$destinationSection?->heading ?: 'Popular destinations'"
                :subtitle="$destinationSection?->content"
            />
            @if ($destinations->isNotEmpty())
                <div class="row g-4">
                    @foreach ($destinations as $destination)
                        <div class="col-md-6 col-lg-4"><x-public.destination-card :destination="$destination" lazy /></div>
                    @endforeach
                </div>
            @else
                <x-public.empty-state title="Featured destinations are coming soon" />
            @endif
        </div>
    </section>

    <section class="section-space why-section" aria-labelledby="why-us-heading">
        <div class="container">
            <x-public.section-heading
                :eyebrow="$whySection?->subheading ?: 'Travel differently'"
                :title="$whySection?->heading ?: 'Why travel with us'"
                :subtitle="$whySection?->content"
            />
            @if (count($whyItems))
                <div class="row g-4">
                    @foreach ($whyItems as $item)
                        <div class="col-md-6 col-lg-3">
                            <article class="why-card h-100">
                                <span class="why-icon" aria-hidden="true">{{ $item['icon'] ?? '✦' }}</span>
                                <h3 class="h4">{{ $item['title'] ?? '' }}</h3>
                                <p class="mb-0">{{ $item['text'] ?? '' }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>
            @else
                <x-public.empty-state title="Our travel promises are being updated" />
            @endif
        </div>
    </section>

    <section class="statistics-section py-5" aria-labelledby="statistics-heading">
        <div class="container">
            <h2 id="statistics-heading" class="visually-hidden">{{ $statisticsSection?->heading ?: 'Sri Soul Ventures statistics' }}</h2>
            @if (count($statistics))
                <div class="row g-4 justify-content-center text-center">
                    @foreach ($statistics as $statistic)
                        <div class="col-6 col-lg-3">
                            <strong class="stat-number">{{ $statistic['value'] ?? '' }}</strong>
                            <span class="stat-label">{{ $statistic['label'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <x-public.empty-state title="Journey statistics are being updated" />
            @endif
        </div>
    </section>

    <section class="section-space section-cream" aria-labelledby="testimonials-heading">
        <div class="container">
            <x-public.section-heading
                :eyebrow="$testimonialSection?->subheading ?: 'Traveller stories'"
                :title="$testimonialSection?->heading ?: 'What our guests say'"
                :subtitle="$testimonialSection?->content"
            />
            @if ($testimonials->isNotEmpty())
                <div class="row g-4">
                    @foreach ($testimonials as $testimonial)
                        <div class="col-md-6 col-lg-4"><x-public.testimonial-card :testimonial="$testimonial" lazy /></div>
                    @endforeach
                </div>
            @else
                <x-public.empty-state title="Traveller stories are coming soon" />
            @endif
        </div>
    </section>

    @if ($customCta)
        <section class="section-space">
            <div class="container">
                <x-public.cta-banner
                    :title="$customCta->heading"
                    :text="$customCta->content ?: $customCta->subheading"
                    :button-text="$customCta->button_text"
                    :button-url="$customCta->button_url ?: route('custom-tours')"
                />
            </div>
        </section>
    @endif

    @if ($whatsappCta)
        <section class="pb-5">
            <div class="container">
                <div class="whatsapp-cta p-4 p-lg-5 d-lg-flex align-items-center justify-content-between gap-4">
                    <div><p class="section-kicker mb-2">{{ $whatsappCta->subheading }}</p><h2 class="display-6 mb-2">{{ $whatsappCta->heading }}</h2><p class="mb-lg-0">{{ $whatsappCta->content }}</p></div>
                    @if ($whatsappCta->button_text && $whatsappCta->button_url)
                        <a class="btn btn-light rounded-pill px-4 py-3 text-nowrap" href="{{ $whatsappCta->button_url }}" target="_blank" rel="noopener">{{ $whatsappCta->button_text }}</a>
                    @endif
                </div>
            </div>
        </section>
    @endif
@endsection
