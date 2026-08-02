@extends('layouts.public', ['title' => $hero?->heading ?: 'Experiences'])

@section('content')
    <x-public.page-hero
        :title="$hero?->heading ?: 'Sri Lankan Experiences'"
        :subtitle="$hero?->content ?: ($hero?->subheading ?: 'Meet the island through nature, culture, flavour and adventure.')"
        :image="$hero?->image_path ? Storage::disk('public')->url($hero->image_path) : null"
    />

    <section class="section-space">
        <div class="container">
            <form class="filter-panel mb-5" method="GET" action="{{ route('experiences.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label" for="experience-search">Search</label>
                        <input class="form-control" id="experience-search" name="search" value="{{ request('search') }}" placeholder="Search experiences or locations">
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label" for="experience-category">Category</label>
                        <select class="form-select" id="experience-category" name="category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label" for="experience-destination">Destination</label>
                        <select class="form-select" id="experience-destination" name="destination"><option value="">All destinations</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected((string) request('destination') === (string) $destination->id)>{{ $destination->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label" for="experience-style">Travel style</label>
                        <select class="form-select" id="experience-style" name="travel_style"><option value="">All styles</option>@foreach($travelStyles as $style)<option value="{{ $style->slug }}" @selected(request('travel_style') === $style->slug)>{{ $style->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label" for="experience-duration">Duration</label>
                        <select class="form-select" id="experience-duration" name="duration">
                            <option value="">Any duration</option>
                            @foreach(['under_4_hours' => 'Under 4 hours', 'half_day' => 'Half day (4–8 hours)', 'full_day' => 'Full day', 'multi_day' => 'Multi-day'] as $value => $label)<option value="{{ $value }}" @selected(request('duration') === $value)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label" for="price-min">Minimum price</label>
                        <input class="form-control" id="price-min" type="number" min="0" step="1" name="price_min" value="{{ request('price_min') }}" placeholder="0">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label" for="price-max">Maximum price</label>
                        <input class="form-control" id="price-max" type="number" min="0" step="1" name="price_max" value="{{ request('price_max') }}" placeholder="Any">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label" for="experience-sort">Sort</label>
                        <select class="form-select" id="experience-sort" name="sort">
                            @foreach(['' => 'Recommended', 'popular' => 'Popular first', 'newest' => 'Newest first', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low'] as $value => $label)<option value="{{ $value }}" @selected(request('sort') === $value)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-5 d-flex gap-2">
                        <button class="btn btn-forest flex-grow-1" type="submit">Apply filters</button>
                        <a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('experiences.index') }}">Reset</a>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">{{ $experiences->total() }} {{ Str::plural('experience', $experiences->total()) }}</h2>
            </div>

            @if ($experiences->isEmpty())
                <x-public.empty-state title="No experiences found" text="Try clearing or broadening your filters." />
            @else
                <div class="row g-4">
                    @foreach ($experiences as $experience)
                        <div class="col-md-6 col-lg-4"><x-public.experience-card :experience="$experience" lazy /></div>
                    @endforeach
                </div>
                <x-public.pagination :paginator="$experiences" />
            @endif
        </div>
    </section>
@endsection
