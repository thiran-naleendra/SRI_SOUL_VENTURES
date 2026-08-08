@props(['package', 'lazy' => false])
<article class="card travel-card package-card">
    <div class="position-relative">
        @if ($package->cover_image)
            <img src="{{ Storage::disk('public')->url($package->cover_image) }}" alt="{{ $package->title }}" width="800" height="520" decoding="async" sizes="(max-width: 767px) 100vw, (max-width: 1199px) 50vw, 33vw" @if($lazy) loading="lazy" @endif>
        @else
            <div class="image-fallback" role="img" aria-label="{{ $package->title }} image placeholder">Sri Lanka Journeys</div>
        @endif
        @if ($package->badge_text)<span class="package-card-badge badge">{{ $package->badge_text }}</span>@endif
    </div>
    <div class="card-body p-4 d-flex flex-column">
        <div class="small text-success fw-bold mb-2">{{ $package->days }} days · {{ $package->nights }} nights</div>
        <h3 class="h4 serif">{{ $package->title }}</h3>
        @if ($package->destinations->isNotEmpty())<p class="small fw-semibold mb-2">{{ $package->destinations->pluck('name')->join(' · ') }}</p>@endif
        <p class="text-secondary">{{ Str::limit($package->short_description, 120) }}</p>
        <div class="package-card-facts small mb-3">
            @if ($package->is_customizable)<span class="text-success fw-semibold"><span aria-hidden="true">⚙</span> Customizable</span>@endif
            <span>Minimum {{ $package->minimum_travelers }} {{ Str::plural('traveller', $package->minimum_travelers) }}</span>
            @if ($package->physical_level)<span>{{ $package->physical_level }}</span>@endif
        </div>
        <div class="d-flex justify-content-between align-items-end gap-3 mt-auto">
            @if ($package->starting_price !== null)<div><small class="d-block text-secondary">From</small><strong>{{ $package->currency }} {{ number_format((float) $package->starting_price, 2) }}</strong></div>@endif
            <a href="{{ route('packages.show', $package) }}" class="btn btn-forest btn-sm">View Details</a>
        </div>
    </div>
</article>
