@extends('layouts.public', ['title' => $sections->get('hero')?->heading ?: 'About Sri Soul Ventures'])

@section('content')
    @php
        $hero = $sections->get('hero');
        $story = $sections->get('story') ?: $sections->get('intro');
        $mission = $sections->get('mission');
        $vision = $sections->get('vision');
        $promise = $sections->get('promise');
        $whySection = $sections->get('why_us');
        $statisticsSection = $sections->get('statistics');
        $teamSection = $sections->get('team');
        $testimonialSection = $sections->get('testimonials');
        $customCta = $sections->get('custom_journey_cta');
        $whatsappCta = $sections->get('whatsapp_cta');
        $heroImage = $hero?->image_path ? Storage::disk('public')->url($hero->image_path) : null;
        $whyItems = is_array($whySection?->settings['items'] ?? null) ? $whySection->settings['items'] : [];
        $statistics = is_array($statisticsSection?->settings['items'] ?? null) ? $statisticsSection->settings['items'] : [];
        $whatsappNumber = preg_replace('/\D+/', '', $websiteSettings?->whatsapp_number ?? '');
        $whatsappUrl = $whatsappCta?->button_url ?: ($whatsappNumber ? 'https://wa.me/'.$whatsappNumber : null);
    @endphp

    <x-public.page-hero :title="$hero?->heading ?: 'The people behind your journey'" :subtitle="$hero?->content ?: ($hero?->subheading ?: 'Local insight, genuine hospitality and a deep love for Sri Lanka.')" :image="$heroImage" />
    <div class="container py-3"><x-public.breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'About us']]" /></div>

    <section class="section-space pt-4"><div class="container"><div class="row g-5 align-items-center">@if($story?->image_path)<div class="col-lg-6"><img class="about-story-image" src="{{ Storage::disk('public')->url($story->image_path) }}" alt="{{ $story->heading ?: 'The Sri Soul Ventures story' }}"></div>@endif<div class="{{ $story?->image_path ? 'col-lg-6' : 'col-lg-8 mx-auto text-center' }}"><x-public.section-heading :eyebrow="$story?->subheading ?: 'Our story'" :title="$story?->heading ?: 'Travel with heart and local perspective'" :subtitle="$story?->content ?: 'We create meaningful journeys that reveal Sri Lanka through its landscapes, culture and people.'" :center="! $story?->image_path" /></div></div></div></section>

    <section class="section-space section-cream"><div class="container"><div class="row g-4">@foreach([['section' => $mission, 'label' => 'Our mission', 'icon' => '✦'], ['section' => $vision, 'label' => 'Our vision', 'icon' => '◉'], ['section' => $promise, 'label' => 'Our promise', 'icon' => '♡']] as $value)<div class="col-md-4"><article class="about-value-card h-100"><span aria-hidden="true">{{ $value['icon'] }}</span><p class="section-kicker">{{ $value['label'] }}</p><h2 class="h3">{{ $value['section']?->heading ?: $value['label'] }}</h2>@if($value['section']?->content || $value['section']?->subheading)<p>{{ $value['section']->content ?: $value['section']->subheading }}</p>@endif</article></div>@endforeach</div></div></section>

    <section class="section-space why-about-section"><div class="container"><x-public.section-heading :eyebrow="$whySection?->subheading ?: 'Travel differently'" :title="$whySection?->heading ?: 'Why travel with us'" :subtitle="$whySection?->content" />@if(count($whyItems))<div class="row g-4">@foreach($whyItems as $item)<div class="col-md-6 col-lg-3"><article class="why-card h-100"><span class="why-icon" aria-hidden="true">{{ $item['icon'] ?? '✦' }}</span><h3 class="h4">{{ $item['title'] ?? '' }}</h3><p class="mb-0">{{ $item['text'] ?? '' }}</p></article></div>@endforeach</div>@else<x-public.empty-state title="Our travel promises are being updated" />@endif</div></section>

    @if (count($statistics))<section class="statistics-section py-5"><div class="container"><div class="row g-4 text-center justify-content-center">@foreach($statistics as $statistic)<div class="col-6 col-lg-3"><strong class="stat-number">{{ $statistic['value'] ?? '' }}</strong><span class="stat-label">{{ $statistic['label'] ?? '' }}</span></div>@endforeach</div></div></section>@endif

    <section class="section-space"><div class="container"><x-public.section-heading :eyebrow="$teamSection?->subheading ?: 'Meet the team'" :title="$teamSection?->heading ?: 'Your local travel specialists'" :subtitle="$teamSection?->content" />@if($team->isNotEmpty())<div class="row g-4 justify-content-center">@foreach($team as $member)<div class="col-sm-6 col-lg-3"><article class="team-public-card h-100">@if($member->profile_image)<img src="{{ Storage::disk('public')->url($member->profile_image) }}" alt="Portrait of {{ $member->name }}" loading="lazy">@endif<div class="p-4"><h3 class="h4 mb-1">{{ $member->name }}</h3><p class="text-success fw-semibold">{{ $member->designation }}</p>@if($member->biography)<p>{{ Str::limit($member->biography, 150) }}</p>@endif<div class="d-flex gap-3">@if($member->linkedin_url)<a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener">LinkedIn</a>@endif @if($member->instagram_url)<a href="{{ $member->instagram_url }}" target="_blank" rel="noopener">Instagram</a>@endif</div></div></article></div>@endforeach</div>@else<x-public.empty-state title="Our team profiles are coming soon" />@endif</div></section>

    <section class="section-space section-cream"><div class="container"><x-public.section-heading :eyebrow="$testimonialSection?->subheading ?: 'Traveller stories'" :title="$testimonialSection?->heading ?: 'What our guests say'" :subtitle="$testimonialSection?->content" />@if($testimonials->isNotEmpty())<div class="row g-4">@foreach($testimonials as $testimonial)<div class="col-md-6 col-lg-4"><x-public.testimonial-card :testimonial="$testimonial" lazy /></div>@endforeach</div>@else<x-public.empty-state title="Traveller stories are coming soon" />@endif</div></section>

    <section class="section-space"><div class="container"><x-public.cta-banner :title="$customCta?->heading ?: 'Let us show you our Sri Lanka'" :text="$customCta?->content" :button-text="$customCta?->button_text ?: 'Plan a custom tour'" :button-url="$customCta?->button_url ?: route('custom-tours')" /></div></section>

    @if ($whatsappUrl)<section class="pb-5"><div class="container"><div class="whatsapp-cta p-4 p-lg-5 d-lg-flex align-items-center justify-content-between gap-4"><div><p class="section-kicker mb-2">{{ $whatsappCta?->subheading ?: 'WhatsApp us' }}</p><h2 class="display-6">{{ $whatsappCta?->heading ?: 'Talk with a local specialist' }}</h2><p class="mb-lg-0">{{ $whatsappCta?->content }}</p></div><a class="btn btn-light rounded-pill px-4 py-3" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">{{ $whatsappCta?->button_text ?: 'Start a conversation' }}</a></div></div></section>@endif
@endsection
