@extends('layouts.public', ['title' => $sections->get('hero')?->heading ?: 'Explore Sri Lanka'])

@section('content')
    @php
        $hero = $sections->get('hero');
    @endphp
    <x-public.page-hero :title="$hero?->heading ?: 'Explore Sri Lanka'" :subtitle="$hero?->content ?: ($hero?->subheading ?: 'A small island with an extraordinary range of landscapes and stories.')" :image="$hero?->image_path ? Storage::disk('public')->url($hero->image_path) : null" />

    <section class="pt-5">
        <div class="container">
            <form class="destination-search" method="GET" action="{{ route('destinations.index') }}"><label class="visually-hidden" for="destination-search">Search destinations</label><input class="form-control form-control-lg" id="destination-search" name="search" value="{{ request('search') }}" placeholder="Search destinations, regions or best travel times"><button class="btn btn-forest" type="submit">Search</button>@if(request()->hasAny(['search', 'region']))<a class="btn btn-link text-success" href="{{ route('destinations.index') }}">Reset</a>@endif</form>

            <nav class="region-navigation mt-4" aria-label="Destination regions">
                <a class="{{ request('region') ? '' : 'active' }}" href="{{ route('destinations.index', request()->except(['region', 'page'])) }}">All regions</a>
                @foreach ($regions as $region)<a class="{{ (string) request('region') === (string) $region->id ? 'active' : '' }}" href="{{ route('destinations.index', [...request()->except(['region', 'page']), 'region' => $region->id]) }}">{{ $region->name }}</a>@endforeach
            </nav>
        </div>
    </section>

    <section class="section-space pt-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="h3 mb-0">{{ $destinations->total() }} {{ Str::plural('destination', $destinations->total()) }}</h2></div>
            @if ($destinations->isEmpty())
                <x-public.empty-state title="No destinations found" text="Try a different search or region." />
            @else
                <div class="row g-4">@foreach($destinations as $destination)<div class="col-md-6 col-lg-4"><x-public.destination-card :destination="$destination" lazy /></div>@endforeach</div>
                <x-public.pagination :paginator="$destinations" />
            @endif
        </div>
    </section>

    <section class="section-space section-cream" aria-labelledby="destination-map-heading">
        <div class="container"><x-public.section-heading eyebrow="Find your place" title="Explore the island" subtitle="Move through Sri Lanka’s regions and open a destination to learn more." />
            @if ($mapDestinations->isNotEmpty())
                <div class="destination-map-layout">
                    <div class="destination-map-canvas" role="img" aria-label="Interactive-style map of Sri Lanka destinations">
                        @foreach ($mapDestinations as $mapDestination)
                            @php
                                $left = max(8, min(92, (((float) $mapDestination->longitude - 79.5) / 2.5) * 84 + 8));
                                $top = max(5, min(92, ((9.9 - (float) $mapDestination->latitude) / 4.2) * 87 + 5));
                            @endphp
                            <a class="map-pin" style="--pin-left:{{ $left }}%;--pin-top:{{ $top }}%" href="{{ route('destinations.show', $mapDestination) }}" title="{{ $mapDestination->name }}"><span></span><strong>{{ $mapDestination->name }}</strong></a>
                        @endforeach
                    </div>
                    <div class="map-destination-list">
                        @foreach ($mapDestinations as $mapDestination)
                            <a href="{{ route('destinations.show', $mapDestination) }}"><span><strong>{{ $mapDestination->name }}</strong><small>{{ $mapDestination->region?->name }}</small></span><span aria-hidden="true">→</span></a>
                        @endforeach
                    </div>
                </div>
            @else
                <x-public.empty-state title="Destination map points are coming soon" />
            @endif
        </div>
    </section>

    <section class="section-space"><div class="container"><x-public.cta-banner :title="$sections->get('custom_journey_cta')?->heading ?: 'Build a journey around the places you love'" :text="$sections->get('custom_journey_cta')?->content" :button-text="$sections->get('custom_journey_cta')?->button_text ?: 'Plan a custom tour'" :button-url="$sections->get('custom_journey_cta')?->button_url ?: route('custom-tours')" /></div></section>
@endsection
