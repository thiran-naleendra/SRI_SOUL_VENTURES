@extends('layouts.public', ['title' => $destination->meta_title ?: $destination->name, 'metaDescription' => $destination->meta_description ?: $destination->short_description, 'ogImage' => $destination->cover_image ? Storage::disk('public')->url($destination->cover_image) : null, 'ogType' => 'article'])

@section('content')
    @php
        $customCta = $sections->get('custom_journey_cta');
        $destinationImages = collect([$destination->cover_image])->merge($destination->images->pluck('image_path'))->filter()->map(fn ($path) => Storage::disk('public')->url($path))->values()->all();
        $destinationSchema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'TouristDestination',
            'name' => $destination->name,
            'description' => $destination->meta_description ?: $destination->short_description,
            'url' => route('destinations.show', $destination),
            'image' => $destinationImages ?: null,
            'touristType' => $destination->region?->name,
            'geo' => $destination->latitude !== null && $destination->longitude !== null ? ['@type' => 'GeoCoordinates', 'latitude' => $destination->latitude, 'longitude' => $destination->longitude] : null,
        ], fn ($value) => $value !== null && $value !== '');
    @endphp
    <x-public.structured-data :data="$destinationSchema" />
    <div class="container py-4"><x-public.breadcrumb :items="['Home' => route('home'), 'Destinations' => route('destinations.index'), $destination->name => '']" /></div>

    @if ($destination->cover_image)
        <div class="container"><img class="detail-cover" src="{{ Storage::disk('public')->url($destination->cover_image) }}" alt="{{ $destination->name }}" width="1600" height="900" decoding="async" fetchpriority="high" sizes="100vw"></div>
    @endif

    @if ($destination->images->isNotEmpty())
        <section class="container pt-4" aria-labelledby="destination-gallery-heading"><h2 class="visually-hidden" id="destination-gallery-heading">{{ $destination->name }} gallery</h2><div class="experience-gallery">@foreach($destination->images as $image)<figure class="mb-0"><img src="{{ Storage::disk('public')->url($image->image_path) }}" alt="{{ $image->alt_text ?: $destination->name }}" loading="lazy" decoding="async" width="900" height="600" sizes="(max-width: 767px) 100vw, 50vw">@if($image->caption)<figcaption class="small text-secondary mt-2">{{ $image->caption }}</figcaption>@endif</figure>@endforeach</div></section>
    @endif

    <section class="section-space pt-5">
        <div class="container"><div class="row g-5"><div class="col-lg-8">
            <p class="section-kicker mb-2">{{ $destination->region->name }}</p><h1 class="display-4">{{ $destination->name }}</h1>@if($destination->short_description)<p class="lead">{{ $destination->short_description }}</p>@endif
            <section class="destination-detail-section"><h2>Overview</h2>@if($destination->full_description)<div class="content-prose">{!! nl2br(e($destination->full_description)) !!}</div>@endif</section>

            @if ($destination->attractions->isNotEmpty())
                <section class="destination-detail-section"><h2>Top attractions</h2><div class="row g-4">@foreach($destination->attractions as $attraction)<div class="col-md-6"><article class="destination-content-card h-100">@if($attraction->image_path)<img src="{{ Storage::disk('public')->url($attraction->image_path) }}" alt="{{ $attraction->title }}" loading="lazy">@endif<div class="p-4"><h3 class="h4">{{ $attraction->title }}</h3>@if($attraction->description)<p class="mb-0">{{ $attraction->description }}</p>@endif</div></article></div>@endforeach</div></section>
            @endif

            @if ($destination->activities->isNotEmpty())
                <section class="destination-detail-section"><h2>Things to do</h2><div class="row g-3">@foreach($destination->activities as $activity)<div class="col-md-6"><article class="activity-card h-100"><span aria-hidden="true">{{ $activity->icon ?: '✦' }}</span><div><h3 class="h5">{{ $activity->title }}</h3>@if($activity->description)<p class="mb-0">{{ $activity->description }}</p>@endif</div></article></div>@endforeach</div></section>
            @endif

            @if ($destination->travelTips->isNotEmpty())
                <section class="destination-detail-section"><h2>Travel tips</h2><div class="accordion destination-tips" id="destinationTips">@foreach($destination->travelTips as $tip)<div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#tip{{ $tip->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">{{ $tip->title }}</button></h3><div id="tip{{ $tip->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#destinationTips"><div class="accordion-body">{{ $tip->description }}</div></div></div>@endforeach</div></section>
            @endif

            @if ($destination->latitude !== null && $destination->longitude !== null)
                <section class="destination-detail-section"><h2>Map</h2><div class="ratio ratio-16x9 rounded-4 overflow-hidden"><iframe title="Map showing {{ $destination->name }}" src="https://www.google.com/maps?q={{ $destination->latitude }},{{ $destination->longitude }}&amp;z=12&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div></section>
            @endif
        </div><aside class="col-lg-4"><div class="destination-plan-card sticky-lg-top"><p class="section-kicker">Plan your visit</p><h2 class="h3">{{ $destination->name }}</h2><dl><dt>Region</dt><dd>{{ $destination->region->name }}</dd>@if($destination->best_time_to_visit)<dt>Best time to visit</dt><dd>{{ $destination->best_time_to_visit }}</dd>@endif</dl><a class="btn btn-forest w-100" href="{{ route('custom-tours') }}">Plan a custom tour</a></div></aside></div></div>
    </section>

    <section class="section-space section-cream"><div class="container"><x-public.section-heading title="Experiences in {{ $destination->name }}" />@if($relatedExperiences->isNotEmpty())<div class="row g-4">@foreach($relatedExperiences as $experience)<div class="col-md-6 col-lg-4"><x-public.experience-card :experience="$experience" lazy /></div>@endforeach</div>@else<x-public.empty-state title="Experiences for this destination are coming soon" />@endif</div></section>

    <section class="section-space"><div class="container"><x-public.section-heading title="Packages visiting {{ $destination->name }}" />@if($relatedPackages->isNotEmpty())<div class="row g-4">@foreach($relatedPackages as $package)<div class="col-md-6 col-lg-4"><x-public.package-card :package="$package" lazy /></div>@endforeach</div>@else<x-public.empty-state title="Packages for this destination are coming soon" />@endif</div></section>

    <section class="pb-5"><div class="container"><x-public.cta-banner :title="$customCta?->heading ?: 'Let this destination inspire your journey'" :text="$customCta?->content" :button-text="$customCta?->button_text ?: 'Plan a custom tour'" :button-url="$customCta?->button_url ?: route('custom-tours')" /></div></section>
@endsection
