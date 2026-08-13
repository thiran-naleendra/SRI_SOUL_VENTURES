@php
    $seoTitle = $title ?? $websiteSettings?->default_meta_title ?? $websiteSettings?->website_name ?? config('app.name');
    $seoDescription = $metaDescription ?? $websiteSettings?->default_meta_description;
    $seoCanonical = $canonical ?? request()->url();
    $seoImage = $ogImage ?? ($websiteSettings?->logo ? Storage::disk('public')->url($websiteSettings->logo) : null);
    $socialUrls = collect([
        $websiteSettings?->facebook_url,
        $websiteSettings?->instagram_url,
        $websiteSettings?->youtube_url,
        $websiteSettings?->linkedin_url,
    ])->filter()->values()->all();
    $organization = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $websiteSettings?->website_name ?? config('app.name'),
        'url' => route('home'),
        'logo' => $websiteSettings?->logo ? Storage::disk('public')->url($websiteSettings->logo) : null,
        'email' => $websiteSettings?->primary_email,
        'telephone' => $websiteSettings?->primary_phone,
        'address' => $websiteSettings?->address ? ['@type' => 'PostalAddress', 'streetAddress' => $websiteSettings->address] : null,
        'sameAs' => $socialUrls ?: null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoTitle }}</title>
    @if ($seoDescription)<meta name="description" content="{{ $seoDescription }}">@endif
    <link rel="canonical" href="{{ $seoCanonical }}">
    @if ($websiteSettings?->favicon)<link rel="icon" href="{{ Storage::disk('public')->url($websiteSettings->favicon) }}">@endif
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    @if ($seoDescription)<meta property="og:description" content="{{ $seoDescription }}">@endif
    <meta property="og:url" content="{{ $seoCanonical }}">
    @if ($seoImage)<meta property="og:image" content="{{ $seoImage }}">@endif
    <meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    @if ($seoDescription)<meta name="twitter:description" content="{{ $seoDescription }}">@endif
    @if ($seoImage)<meta name="twitter:image" content="{{ $seoImage }}">@endif
    <x-public.structured-data :data="$organization" />
    @stack('structured-data')
    @vite(['resources/css/public.css', 'resources/js/public.js'])
    @stack('styles')
</head>
<body class="public-site">
    <x-public.header :settings="$websiteSettings" />
    @if (session('success'))
        <div class="container pt-3"><div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div></div>
    @endif
    @if (session('error'))
        <div class="container pt-3"><div class="alert alert-danger" role="alert">{{ session('error') }}</div></div>
    @endif
    <main>@yield('content')</main>
    <x-public.footer :settings="$websiteSettings" />
    <x-public.whatsapp-button :number="$websiteSettings?->whatsapp_number" />
</body>
</html>
