@props(['items' => []])
@php
    $schemaItems = collect($items)->map(function ($item, $key) {
        return [
            'label' => is_array($item) ? $item['label'] : $key,
            'url' => is_array($item) ? ($item['url'] ?? null) : $item,
        ];
    })->values();
@endphp
<nav aria-label="Breadcrumb">
    <ol class="breadcrumb">
        @foreach ($items as $key => $item)
            @php
                $label = is_array($item) ? $item['label'] : $key;
                $url = is_array($item) ? ($item['url'] ?? null) : $item;
            @endphp
            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" @if ($loop->last) aria-current="page" @endif>
                @if (! $loop->last && $url)
                    <a href="{{ $url }}">{{ $label }}</a>
                @else
                    {{ $label }}
                @endif
            </li>
        @endforeach
    </ol>
</nav>
<x-public.structured-data :data="[
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $schemaItems->map(fn ($item, $index) => [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $item['label'],
        'item' => $item['url'] ?: request()->url(),
    ])->all(),
]" />
