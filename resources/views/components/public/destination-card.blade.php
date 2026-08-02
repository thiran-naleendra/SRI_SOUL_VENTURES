@props(['destination', 'lazy' => false])
<article class="card travel-card destination-card">
    @if ($destination->cover_image)
        <img src="{{ Storage::disk('public')->url($destination->cover_image) }}" alt="{{ $destination->name }}" width="800" height="520" decoding="async" sizes="(max-width: 767px) 100vw, (max-width: 1199px) 50vw, 33vw" @if($lazy) loading="lazy" @endif>
    @else
        <div class="image-fallback" role="img" aria-label="{{ $destination->name }} image placeholder">Discover</div>
    @endif
    <div class="card-body p-4 d-flex flex-column">
        <small class="text-success fw-bold">{{ $destination->region?->name }}</small>
        <h3 class="h4 serif">{{ $destination->name }}</h3>
        <p class="text-secondary">{{ Str::limit($destination->short_description, 110) }}</p>
        @if ($destination->best_time_to_visit)<p class="destination-best-time"><span aria-hidden="true">☀</span><span><small>Best time to visit</small><strong>{{ $destination->best_time_to_visit }}</strong></span></p>@endif
        <a href="{{ route('destinations.show', $destination) }}" class="btn btn-forest btn-sm mt-auto align-self-start">Discover</a>
    </div>
</article>
