@props(['src' => null, 'alt' => 'Image preview'])
<div {{ $attributes->class('image-preview') }}>
    @if ($src)<img src="{{ $src }}" alt="{{ $alt }}">@else<div class="text-center text-secondary p-4"><div class="fs-2" aria-hidden="true">▧</div><small>No image selected</small></div>@endif
</div>
