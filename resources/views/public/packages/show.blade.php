@extends('layouts.public', ['title' => $package->meta_title ?: $package->title, 'metaDescription' => $package->meta_description ?: $package->short_description, 'ogImage' => $package->cover_image ? Storage::disk('public')->url($package->cover_image) : null, 'ogType' => 'article'])

@section('content')
    @php
        $customCta = $sections->get('custom_journey_cta');
        $whatsappCta = $sections->get('whatsapp_cta');
        $whatsappNumber = preg_replace('/\D+/', '', $websiteSettings?->whatsapp_number ?? '');
        $whatsappUrl = $whatsappCta?->button_url ?: ($whatsappNumber ? 'https://wa.me/'.$whatsappNumber : null);
        $availabilityUrl = route('contact', ['package' => $package->slug]);
        $packageSchema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => $package->title,
            'description' => $package->meta_description ?: $package->short_description,
            'url' => route('packages.show', $package),
            'image' => $package->cover_image ? Storage::disk('public')->url($package->cover_image) : null,
            'touristType' => $package->travelStyles->pluck('name')->values()->all() ?: null,
            'itinerary' => $package->itineraries->isNotEmpty() ? ['@type' => 'ItemList', 'itemListElement' => $package->itineraries->map(fn ($day) => ['@type' => 'ListItem', 'position' => $day->day_number, 'name' => $day->title])->all()] : null,
            'offers' => $package->starting_price !== null ? ['@type' => 'Offer', 'price' => (float) $package->starting_price, 'priceCurrency' => $package->currency, 'url' => route('packages.show', $package), 'availability' => 'https://schema.org/InStock'] : null,
        ], fn ($value) => $value !== null && $value !== '');
    @endphp
    <x-public.structured-data :data="$packageSchema" />

    <div class="container py-4"><x-public.breadcrumb :items="['Home' => route('home'), 'Packages' => route('packages.index'), $package->title => '']" /></div>

    <section class="package-detail-hero {{ $package->cover_image ? '' : 'package-detail-hero-fallback' }}" @if($package->cover_image) style="--package-hero:url('{{ Storage::disk('public')->url($package->cover_image) }}')" @endif>
        <div class="container position-relative">
            <div class="col-lg-9">
                @if ($package->badge_text)<span class="badge package-hero-badge mb-3">{{ $package->badge_text }}</span>@endif
                <h1 class="display-3">{{ $package->title }}</h1>
                @if ($package->destinations->isNotEmpty())<p class="package-destinations">{{ $package->destinations->pluck('name')->join(' · ') }}</p>@endif
                @if ($package->short_description)<p class="lead col-lg-9">{{ $package->short_description }}</p>@endif
                <div class="package-hero-facts">
                    <span>{{ $package->days }} days · {{ $package->nights }} nights</span>
                    <span>{{ $package->destinations->count() }} {{ Str::plural('destination', $package->destinations->count()) }}</span>
                    @if ($package->tour_type)<span>{{ $package->tour_type }}</span>@endif
                    <span>{{ $package->is_customizable ? 'Customizable' : 'Fixed itinerary' }}</span>
                </div>
            </div>
        </div>
    </section>

    <nav class="package-anchor-nav" aria-label="Package sections"><div class="container"><div class="d-flex overflow-x-auto">@foreach(['overview' => 'Overview', 'itinerary' => 'Itinerary', 'inclusions' => 'Inclusions', 'exclusions' => 'Exclusions', 'accommodation' => 'Accommodation', 'reviews' => 'Reviews', 'faq' => 'FAQ'] as $anchor => $label)<a href="#{{ $anchor }}">{{ $label }}</a>@endforeach</div></div></nav>

    <section class="section-space pt-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8 package-detail-content">
                    <section id="overview" class="package-section scroll-anchor"><h2>Overview</h2>@if($package->full_description)<div class="content-prose">{!! nl2br(e($package->full_description)) !!}</div>@endif<div class="overview-grid mt-4">@if($package->perfect_for)<div><strong>Perfect for</strong><span>{{ $package->perfect_for }}</span></div>@endif @if($package->travelStyles->isNotEmpty())<div><strong>Travel styles</strong><span>{{ $package->travelStyles->pluck('name')->join(', ') }}</span></div>@endif @if($package->physical_level)<div><strong>Physical level</strong><span>{{ $package->physical_level }}</span></div>@endif<div><strong>Group size</strong><span>{{ $package->minimum_travelers }}@if($package->maximum_travelers)–{{ $package->maximum_travelers }}@else+@endif travellers</span></div></div></section>

                    @if ($package->itineraries->isNotEmpty())
                        <section id="itinerary" class="package-section scroll-anchor"><h2>Itinerary</h2><div class="itinerary-timeline">@foreach($package->itineraries as $day)<article class="itinerary-day"><div class="itinerary-marker">{{ $day->day_number }}</div><div class="itinerary-card">@if($day->image_path)<img src="{{ Storage::disk('public')->url($day->image_path) }}" alt="Day {{ $day->day_number }}: {{ $day->title }}" loading="lazy">@endif<div class="p-4"><p class="section-kicker mb-1">Day {{ $day->day_number }}</p><h3 class="h4">{{ $day->title }}</h3>@if($day->description)<div>{!! nl2br(e($day->description)) !!}</div>@endif<div class="itinerary-meta mt-3">@if($day->destination_name)<span><strong>Destination:</strong> {{ $day->destination_name }}</span>@endif @if($day->accommodation_name)<span><strong>Stay:</strong> {{ $day->accommodation_name }}</span>@endif @if($day->meals)<span><strong>Meals:</strong> {{ $day->meals }}</span>@endif</div></div></div></article>@endforeach</div></section>
                    @endif

                    @if ($package->inclusions->isNotEmpty())<section id="inclusions" class="package-section scroll-anchor"><h2>What’s included</h2><ul class="detail-check-list package-list">@foreach($package->inclusions as $item)<li>{{ $item->item }}</li>@endforeach</ul></section>@endif
                    @if ($package->exclusions->isNotEmpty())<section id="exclusions" class="package-section scroll-anchor"><h2>What’s not included</h2><ul class="detail-cross-list package-list">@foreach($package->exclusions as $item)<li>{{ $item->item }}</li>@endforeach</ul></section>@endif

                    @if ($package->accommodation_summary || $package->transportation_summary)
                        <section id="accommodation" class="package-section scroll-anchor"><h2>Accommodation and transportation</h2><div class="row g-4">@if($package->accommodation_summary)<div class="col-md-6"><div class="info-panel h-100"><h3 class="h4">Accommodation</h3><div>{!! nl2br(e($package->accommodation_summary)) !!}</div></div></div>@endif @if($package->transportation_summary)<div class="col-md-6"><div class="info-panel h-100"><h3 class="h4">Transportation</h3><div>{!! nl2br(e($package->transportation_summary)) !!}</div></div></div>@endif</div></section>
                    @endif

                    @if ($package->reviews->isNotEmpty())
                        <section id="reviews" class="package-section scroll-anchor"><h2>Traveller reviews</h2><div class="row g-4">@foreach($package->reviews as $review)<div class="col-md-6"><figure class="review-card h-100">@if($review->customer_image)<img src="{{ Storage::disk('public')->url($review->customer_image) }}" alt="{{ $review->customer_name }}" loading="lazy">@endif<div class="text-warning" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}</div><blockquote>“{{ $review->review }}”</blockquote><figcaption><strong>{{ $review->customer_name }}</strong>@if($review->country)<span>{{ $review->country }}</span>@endif</figcaption></figure></div>@endforeach</div></section>
                    @endif

                    @if ($package->faqs->isNotEmpty())
                        <section id="faq" class="package-section scroll-anchor"><h2>Frequently asked questions</h2><div class="accordion package-faq" id="packageFaq">@foreach($package->faqs as $faq)<div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $faq->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="faq{{ $faq->id }}">{{ $faq->question }}</button></h3><div id="faq{{ $faq->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#packageFaq"><div class="accordion-body">{!! nl2br(e($faq->answer)) !!}</div></div></div>@endforeach</div></section>
                    @endif

                    @if ($package->highlights->isNotEmpty())
                        <section class="package-section"><h2>Journey highlights</h2><div class="package-highlights">@foreach($package->highlights as $highlight)<figure>@if($highlight->image_path)<img src="{{ Storage::disk('public')->url($highlight->image_path) }}" alt="{{ $highlight->alt_text ?: $highlight->title }}" loading="lazy">@endif<figcaption>{{ $highlight->title }}</figcaption></figure>@endforeach</div></section>
                    @endif
                </div>

                <aside class="col-lg-4 order-first order-lg-last"><div class="package-price-card sticky-lg-top"><p class="small text-uppercase fw-bold text-success mb-1">Starting from</p>@if($package->starting_price !== null)<p class="package-price">{{ $package->currency }} {{ number_format((float) $package->starting_price, 2) }}</p><p class="text-secondary">Per person</p>@endif @if($package->price_note)<p>{{ $package->price_note }}</p>@endif<button class="btn btn-forest w-100 mb-2" type="button" data-bs-toggle="modal" data-bs-target="#availabilityModal">Check Availability</button><a class="btn btn-outline-success rounded-pill w-100 mb-4" href="{{ route('custom-tours', ['package' => $package->slug]) }}">Customize This Tour</a><div class="price-assurances"><p><strong>Best price</strong><span>Book directly with our local team.</span></p>@if($package->cancellation_policy)<p><strong>Cancellation policy</strong><span>{{ Str::limit($package->cancellation_policy, 150) }}</span></p>@endif @if($package->support_text)<p><strong>Local support</strong><span>{{ $package->support_text }}</span></p>@endif</div><button class="btn btn-link text-success p-0" type="button" data-share-button data-share-title="{{ $package->title }}" data-share-url="{{ route('packages.show', $package) }}">Share this tour</button></div></aside>
            </div>
        </div>
    </section>

    @if ($relatedPackages->isNotEmpty())<section class="section-space section-cream"><div class="container"><x-public.section-heading title="Related packages" subtitle="More journeys you may enjoy."/><div class="row g-4">@foreach($relatedPackages as $related)<div class="col-md-6 col-lg-4"><x-public.package-card :package="$related" lazy /></div>@endforeach</div></div></section>@endif

    <section class="section-space"><div class="container"><div class="package-final-cta p-4 p-lg-5"><div><p class="section-kicker">Ready when you are</p><h2 class="display-5">{{ $customCta?->heading ?: $package->title }}</h2>@if($customCta?->content)<p class="lead">{{ $customCta->content }}</p>@endif</div><div class="d-flex flex-wrap gap-3"><button class="btn btn-light rounded-pill px-4 py-3" type="button" data-bs-toggle="modal" data-bs-target="#availabilityModal">Check Availability</button>@if($whatsappUrl)<a class="btn btn-outline-light rounded-pill px-4 py-3" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Chat on WhatsApp</a>@endif</div></div></div></section>

    <div class="modal fade" id="availabilityModal" tabindex="-1" aria-labelledby="availabilityModalLabel" aria-hidden="true" @if($errors->any()) data-open-on-errors @endif>
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered modal-fullscreen-sm-down availability-modal"><div class="modal-content rounded-4 border-0"><div class="modal-header p-4"><div><p class="section-kicker mb-1">{{ $package->title }}</p><h2 class="modal-title h3" id="availabilityModalLabel">Check availability</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><form method="POST" action="{{ route('packages.availability.store', $package) }}">@csrf<div class="modal-body p-4">@if($errors->any())<div class="alert alert-danger" role="alert">Please review the highlighted fields.</div>@endif<div class="honeypot" aria-hidden="true"><label for="website">Website</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div><div class="row g-3"><div class="col-12 col-md-6"><x-public.form-field name="customer_name" label="Customer name" required /></div><div class="col-12 col-md-6"><x-public.form-field name="email" label="Email" type="email" required /></div><div class="col-12 col-md-6"><x-public.form-field name="phone" label="Phone" type="tel" /></div><div class="col-12 col-md-6"><x-public.form-field name="whatsapp_number" label="WhatsApp number" type="tel" /></div><div class="col-12 col-md-6"><x-public.form-field name="country" label="Country" /></div><div class="col-12 col-sm-6 col-md-3"><x-public.form-field name="preferred_start_date" label="Preferred start" type="date" :min="now()->toDateString()" required /></div><div class="col-12 col-sm-6 col-md-3"><x-public.form-field name="preferred_end_date" label="Preferred end" type="date" :min="now()->toDateString()" /></div><div class="col-6 col-md-3"><x-public.form-field name="adults" label="Adults" type="number" min="1" :value="1" required /></div><div class="col-6 col-md-3"><x-public.form-field name="children" label="Children" type="number" min="0" :value="0" required /></div><div class="col-12"><x-public.textarea name="message" label="Message" rows="4" /></div></div></div><div class="modal-footer p-4"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button><button class="btn btn-forest" type="submit">Send availability request</button></div></form></div></div>
    </div>
@endsection
