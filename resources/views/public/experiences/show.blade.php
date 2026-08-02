@extends('layouts.public', [
    'title' => $experience->meta_title ?: $experience->title,
    'metaDescription' => $experience->meta_description ?: $experience->short_description,
    'ogImage' => $experience->cover_image ? Storage::disk('public')->url($experience->cover_image) : null,
    'ogType' => 'article',
])

@section('content')
    @php
        $customCta = $sections->get('custom_journey_cta');
        $whatsappCta = $sections->get('whatsapp_cta');
        $whatsappNumber = preg_replace('/\D+/', '', $websiteSettings?->whatsapp_number ?? '');
        $whatsappUrl = $whatsappCta?->button_url ?: ($whatsappNumber ? 'https://wa.me/'.$whatsappNumber : null);
    @endphp

    <div class="container py-4">
        <x-public.breadcrumb :items="['Home' => route('home'), 'Experiences' => route('experiences.index'), $experience->title => '']" />
    </div>

    @if ($experience->cover_image)
        <div class="container">
            <img class="detail-cover" src="{{ Storage::disk('public')->url($experience->cover_image) }}" alt="{{ $experience->title }}" width="1600" height="900" decoding="async" fetchpriority="high" sizes="100vw">
        </div>
    @endif

    @if ($experience->images->isNotEmpty())
        <section class="container pt-4" aria-labelledby="experience-gallery-heading">
            <h2 class="visually-hidden" id="experience-gallery-heading">{{ $experience->title }} gallery</h2>
            <div class="experience-gallery">
                @foreach ($experience->images as $image)
                    <figure class="mb-0">
                        <img src="{{ Storage::disk('public')->url($image->image_path) }}" alt="{{ $image->alt_text ?: $experience->title }}" loading="lazy" decoding="async" width="900" height="600" sizes="(max-width: 767px) 100vw, 50vw">
                        @if ($image->caption)<figcaption class="small text-secondary mt-2">{{ $image->caption }}</figcaption>@endif
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    <section class="section-space pt-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge badge-soft">{{ $experience->category->name }}</span>
                        @if ($experience->badge_text)<span class="badge text-bg-warning">{{ $experience->badge_text }}</span>@endif
                        @foreach ($experience->travelStyles as $style)<span class="badge rounded-pill text-bg-light">{{ $style->name }}</span>@endforeach
                    </div>
                    <h1 class="display-4">{{ $experience->title }}</h1>
                    <div class="experience-meta d-flex flex-wrap gap-4 my-4">
                        <span><strong>Location:</strong> {{ $experience->location ?: $experience->destination->name }}</span>
                        @if ($experience->duration_value)<span><strong>Duration:</strong> {{ $experience->duration_value }} {{ Str::plural($experience->duration_unit, $experience->duration_value) }}</span>@endif
                        @if ($experience->starting_price !== null)<span><strong>From:</strong> {{ $experience->currency }} {{ number_format((float) $experience->starting_price, 2) }}</span>@endif
                    </div>
                    @if ($experience->short_description)<p class="lead">{{ $experience->short_description }}</p>@endif
                    @if ($experience->full_description)<div class="content-prose">{!! nl2br(e($experience->full_description)) !!}</div>@endif

                    @if ($experience->highlights->isNotEmpty())
                        <div class="detail-content-block"><h2 class="h3">Highlights</h2><ul class="detail-check-list">@foreach($experience->highlights as $item)<li>{{ $item->item }}</li>@endforeach</ul></div>
                    @endif

                    <div class="row g-4">
                        @if ($experience->inclusions->isNotEmpty())
                            <div class="col-md-6"><div class="detail-content-block h-100"><h2 class="h3">What’s included</h2><ul class="detail-check-list">@foreach($experience->inclusions as $item)<li>{{ $item->item }}</li>@endforeach</ul></div></div>
                        @endif
                        @if ($experience->exclusions->isNotEmpty())
                            <div class="col-md-6"><div class="detail-content-block h-100"><h2 class="h3">Not included</h2><ul class="detail-cross-list">@foreach($experience->exclusions as $item)<li>{{ $item->item }}</li>@endforeach</ul></div></div>
                        @endif
                    </div>

                    @if ($experience->important_information)
                        <div class="detail-content-block important-panel"><h2 class="h3">Important information</h2><div class="content-prose">{!! nl2br(e($experience->important_information)) !!}</div></div>
                    @endif

                    @if ($experience->latitude !== null && $experience->longitude !== null)
                        <div class="detail-content-block">
                            <h2 class="h3">Location map</h2>
                            <div class="ratio ratio-16x9 rounded-4 overflow-hidden">
                                <iframe title="Map showing {{ $experience->title }}" src="https://www.google.com/maps?q={{ $experience->latitude }},{{ $experience->longitude }}&amp;z=14&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                            </div>
                        </div>
                    @endif
                </div>

                <aside class="col-lg-4">
                    <div class="card rounded-panel sticky-lg-top" style="top: 100px">
                        <div class="card-body p-4">
                            <p class="text-success fw-bold mb-2">{{ $experience->destination->name }}</p>
                            @if ($experience->starting_price !== null)<p class="h2 serif mb-1">{{ $experience->currency }} {{ number_format((float) $experience->starting_price, 2) }}</p><p class="text-secondary">Starting price per person</p>@endif
                            @if ($experience->duration_value)<p><strong>{{ $experience->duration_value }} {{ Str::plural($experience->duration_unit, $experience->duration_value) }}</strong></p>@endif
                            <a class="btn btn-forest w-100 mb-3" href="{{ $customCta?->button_url ?: route('custom-tours') }}">{{ $customCta?->button_text ?: 'Plan a custom tour' }}</a>
                            @if ($whatsappUrl)<a class="btn btn-outline-success rounded-pill w-100" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">{{ $whatsappCta?->button_text ?: 'Chat on WhatsApp' }}</a>@endif
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="section-space section-cream" aria-labelledby="related-experiences-heading">
        <div class="container">
            <x-public.section-heading title="Related experiences" subtitle="More ways to discover this side of Sri Lanka." />
            @if ($relatedExperiences->isNotEmpty())
                <div class="row g-4">@foreach($relatedExperiences as $related)<div class="col-md-6 col-lg-4"><x-public.experience-card :experience="$related" lazy /></div>@endforeach</div>
            @else
                <x-public.empty-state title="More related experiences are coming soon" />
            @endif
        </div>
    </section>

    <section class="section-space">
        <div class="container">
            <x-public.cta-banner
                :title="$customCta?->heading ?: 'Make this experience part of your journey'"
                :text="$customCta?->content ?: $customCta?->subheading"
                :button-text="$customCta?->button_text ?: 'Plan my journey'"
                :button-url="$customCta?->button_url ?: route('custom-tours')"
            />
        </div>
    </section>

    @if ($whatsappUrl)
        <section class="pb-5">
            <div class="container">
                <div class="whatsapp-cta p-4 p-lg-5 d-lg-flex align-items-center justify-content-between gap-4">
                    <div><p class="section-kicker mb-2">{{ $whatsappCta?->subheading ?: 'WhatsApp us' }}</p><h2 class="display-6 mb-2">{{ $whatsappCta?->heading ?: 'Have a question about this experience?' }}</h2><p class="mb-lg-0">{{ $whatsappCta?->content ?: 'Talk directly with one of our local travel specialists.' }}</p></div>
                    <a class="btn btn-light rounded-pill px-4 py-3 text-nowrap" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">{{ $whatsappCta?->button_text ?: 'Start a conversation' }}</a>
                </div>
            </div>
        </section>
    @endif
@endsection
