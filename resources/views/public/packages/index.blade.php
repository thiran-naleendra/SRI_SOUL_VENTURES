@extends('layouts.public', ['title' => $sections->get('hero')?->heading ?: 'Sri Lanka Travel Packages'])

@section('content')
    @php($hero = $sections->get('hero'))
    <x-public.page-hero
        :title="$hero?->heading ?: 'Sri Lanka Travel Packages'"
        :subtitle="$hero?->content ?: ($hero?->subheading ?: 'Thoughtfully planned routes with room to make them your own.')"
        :image="$hero?->image_path ? Storage::disk('public')->url($hero->image_path) : null"
    />

    <section class="section-space">
        <div class="container">
            <form class="filter-panel mb-5" method="GET" action="{{ route('packages.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4"><label class="form-label" for="package-search">Search</label><input class="form-control" id="package-search" name="search" value="{{ request('search') }}" placeholder="Search packages or destinations"></div>
                    <div class="col-md-6 col-lg-2"><label class="form-label" for="package-duration">Duration</label><select class="form-select" id="package-duration" name="duration"><option value="">Any duration</option>@foreach(['short' => '1–3 days', 'week' => '4–7 days', 'two_weeks' => '8–14 days', 'extended' => '15+ days'] as $value => $label)<option value="{{ $value }}" @selected(request('duration') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-6 col-lg-2"><label class="form-label" for="package-style">Travel style</label><select class="form-select" id="package-style" name="travel_style"><option value="">All styles</option>@foreach($travelStyles as $style)<option value="{{ $style->slug }}" @selected(request('travel_style') === $style->slug)>{{ $style->name }}</option>@endforeach</select></div>
                    <div class="col-md-6 col-lg-2"><label class="form-label" for="package-destination">Destination</label><select class="form-select" id="package-destination" name="destination"><option value="">All destinations</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected((string) request('destination') === (string) $destination->id)>{{ $destination->name }}</option>@endforeach</select></div>
                    <div class="col-md-6 col-lg-2"><label class="form-label" for="package-travelers">Travellers</label><input class="form-control" id="package-travelers" type="number" min="1" name="travelers" value="{{ request('travelers') }}" placeholder="Any group"></div>
                    <div class="col-6 col-md-3 col-lg-2"><label class="form-label" for="budget-min">Minimum budget</label><input class="form-control" id="budget-min" type="number" min="0" step="1" name="budget_min" value="{{ request('budget_min') }}" placeholder="0"></div>
                    <div class="col-6 col-md-3 col-lg-2"><label class="form-label" for="budget-max">Maximum budget</label><input class="form-control" id="budget-max" type="number" min="0" step="1" name="budget_max" value="{{ request('budget_max') }}" placeholder="Any"></div>
                    <div class="col-md-6 col-lg-3"><label class="form-label" for="package-sort">Sort</label><select class="form-select" id="package-sort" name="sort">@foreach(['' => 'Recommended', 'popular' => 'Popular first', 'newest' => 'Newest first', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'duration_asc' => 'Shortest first'] as $value => $label)<option value="{{ $value }}" @selected(request('sort') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-6 col-lg-5 d-flex gap-2"><button class="btn btn-forest flex-grow-1" type="submit">Find packages</button><a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('packages.index') }}">Reset</a></div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="h4 mb-0">{{ $packages->total() }} {{ Str::plural('package', $packages->total()) }}</h2></div>
            @if ($packages->isEmpty())
                <x-public.empty-state title="No packages found" text="Try clearing or broadening your filters, or let us create a custom journey." />
            @else
                <div class="row g-4">@foreach($packages as $package)<div class="col-md-6 col-lg-4"><x-public.package-card :package="$package" lazy /></div>@endforeach</div>
                <x-public.pagination :paginator="$packages" />
            @endif
        </div>
    </section>

    <section class="pb-5"><div class="container"><x-public.cta-banner :title="$sections->get('custom_journey_cta')?->heading ?: 'Looking for something made just for you?'" :text="$sections->get('custom_journey_cta')?->content" :button-text="$sections->get('custom_journey_cta')?->button_text ?: 'Plan a custom journey'" :button-url="$sections->get('custom_journey_cta')?->button_url ?: route('custom-tours')" /></div></section>
@endsection
