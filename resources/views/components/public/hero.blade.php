@props([
    'title',
    'highlightedText' => null,
    'subtitle' => null,
    'image' => null,
    'buttonText' => null,
    'buttonUrl' => null,
    'secondaryButtonText' => null,
    'secondaryButtonUrl' => null,
])
@php
    $highlightPosition = $highlightedText ? mb_stripos($title, $highlightedText) : false;
@endphp
<section class="hero" style="--hero-image:url('{{ $image ?: asset('images/travel-placeholder.jpg') }}')">
    <div class="container position-relative">
        <div class="col-lg-8 col-xl-7">
            <h1 class="display-2 fw-bold">
                @if ($highlightPosition !== false)
                    {{ mb_substr($title, 0, $highlightPosition) }}<span class="hero-highlight">{{ mb_substr($title, $highlightPosition, mb_strlen($highlightedText)) }}</span>{{ mb_substr($title, $highlightPosition + mb_strlen($highlightedText)) }}
                @else
                    {{ $title }}
                @endif
            </h1>
            @if ($subtitle)<p class="lead col-lg-10 mt-4">{{ $subtitle }}</p>@endif
            @if ($buttonText || $secondaryButtonText)
                <div class="d-flex flex-wrap gap-3 mt-4">
                    @if ($buttonText)<a class="btn btn-light rounded-pill px-4 py-3" href="{{ $buttonUrl }}">{{ $buttonText }}</a>@endif
                    @if ($secondaryButtonText)<a class="btn btn-outline-light rounded-pill px-4 py-3" href="{{ $secondaryButtonUrl }}">{{ $secondaryButtonText }}</a>@endif
                </div>
            @endif
        </div>
    </div>
</section>
