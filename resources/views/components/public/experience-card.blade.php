@props(['experience', 'lazy' => false, 'showFavourite' => false])
<article class="card travel-card position-relative">
    @if ($showFavourite)
        <span class="favourite-icon" aria-label="Save {{ $experience->title }} to favourites" role="img">♡</span>
    @endif
    @if ($experience->cover_image)
        <img src="{{ Storage::disk('public')->url($experience->cover_image) }}" alt="{{ $experience->title }}" width="800" height="520" decoding="async" sizes="(max-width: 767px) 100vw, (max-width: 1199px) 50vw, 33vw" @if ($lazy) loading="lazy" @endif>
    @else
        <div class="image-fallback" role="img" aria-label="{{ $experience->title }} image placeholder">Explore Sri Lanka</div>
    @endif
    <div class="card-body p-4">
        <div class="small text-success fw-bold mb-2">{{ collect([$experience->destination?->name, $experience->category?->name])->filter()->join(' · ') }}</div>
        @if ($experience->badge_text)<span class="badge badge-soft mb-2">{{ $experience->badge_text }}</span>@endif
        <h3 class="h4 serif">{{ $experience->title }}</h3>
        <p class="text-secondary">{{ Str::limit($experience->short_description, 110) }}</p>
        <div class="d-flex justify-content-between align-items-center gap-3">
            @if ($experience->starting_price)<strong>{{ $experience->currency }} {{ number_format((float) $experience->starting_price, 2) }}</strong>@endif
            <a href="{{ route('experiences.show', $experience) }}" class="stretched-link text-success fw-bold text-decoration-none">Explore <span aria-hidden="true">→</span></a>
        </div>
    </div>
</article>
