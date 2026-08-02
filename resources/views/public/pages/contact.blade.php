@extends('layouts.public', ['title' => $sections->get('hero')?->heading ?: 'Contact Sri Soul Ventures'])

@section('content')
    @php
        $hero = $sections->get('hero');
        $detailsSection = $sections->get('contact_details');
        $formSection = $sections->get('form');
        $faqSection = $sections->get('faqs');
        $heroImage = $hero?->image_path ? Storage::disk('public')->url($hero->image_path) : null;
        $socialLinks = collect(['facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'youtube_url' => 'YouTube', 'linkedin_url' => 'LinkedIn'])->filter(fn ($label, $field) => filled($websiteSettings?->{$field}));
    @endphp

    <x-public.page-hero :title="$hero?->heading ?: 'Let’s plan something unforgettable'" :subtitle="$hero?->content ?: ($hero?->subheading ?: 'Ask a question or start a conversation with our Sri Lanka travel team.')" :image="$heroImage" />
    <div class="container py-3"><x-public.breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Contact']]" /></div>

    <section class="section-space pt-4">
        <div class="container"><x-public.section-heading :eyebrow="$detailsSection?->subheading ?: 'Get in touch'" :title="$detailsSection?->heading ?: 'We are here to help'" :subtitle="$detailsSection?->content" />
            <div class="row g-4 justify-content-center">
                @if($websiteSettings?->address)<div class="col-md-6 col-lg-3"><article class="contact-detail-card h-100"><span aria-hidden="true">⌖</span><h2 class="h5">Address</h2><p>{{ $websiteSettings->address }}</p></article></div>@endif
                @if($websiteSettings?->primary_phone)<div class="col-md-6 col-lg-3"><article class="contact-detail-card h-100"><span aria-hidden="true">☎</span><h2 class="h5">Phone</h2><a href="tel:{{ preg_replace('/[^+0-9]/', '', $websiteSettings->primary_phone) }}">{{ $websiteSettings->primary_phone }}</a>@if($websiteSettings->secondary_phone)<a class="d-block mt-1" href="tel:{{ preg_replace('/[^+0-9]/', '', $websiteSettings->secondary_phone) }}">{{ $websiteSettings->secondary_phone }}</a>@endif</article></div>@endif
                @if($websiteSettings?->primary_email)<div class="col-md-6 col-lg-3"><article class="contact-detail-card h-100"><span aria-hidden="true">✉</span><h2 class="h5">Email</h2><a href="mailto:{{ $websiteSettings->primary_email }}">{{ $websiteSettings->primary_email }}</a>@if($websiteSettings->secondary_email)<a class="d-block mt-1" href="mailto:{{ $websiteSettings->secondary_email }}">{{ $websiteSettings->secondary_email }}</a>@endif</article></div>@endif
                @if($websiteSettings?->whatsapp_number)<div class="col-md-6 col-lg-3"><article class="contact-detail-card h-100"><span aria-hidden="true">◉</span><h2 class="h5">WhatsApp</h2><a href="https://wa.me/{{ preg_replace('/\D+/', '', $websiteSettings->whatsapp_number) }}" target="_blank" rel="noopener">{{ $websiteSettings->whatsapp_number }}</a></article></div>@endif
                @if($websiteSettings?->business_hours)<div class="col-md-6 col-lg-3"><article class="contact-detail-card h-100"><span aria-hidden="true">◷</span><h2 class="h5">Business hours</h2><p>{{ $websiteSettings->business_hours }}</p></article></div>@endif
            </div>
        </div>
    </section>

    <section class="section-space section-cream"><div class="container"><div class="row g-5 align-items-start">
        <div class="col-lg-5"><x-public.section-heading :eyebrow="$formSection?->subheading ?: 'Send a message'" :title="$formSection?->heading ?: 'Tell us how we can help'" :subtitle="$formSection?->content" :center="false" />@if($socialLinks->isNotEmpty())<div class="contact-socials"><h2 class="h5">Follow Sri Soul Ventures</h2><div class="d-flex flex-wrap gap-2">@foreach($socialLinks as $field => $label)<a href="{{ $websiteSettings->{$field} }}" target="_blank" rel="noopener">{{ $label }}</a>@endforeach</div></div>@endif</div>
        <div class="col-lg-7"><div class="card custom-tour-form-card"><div class="card-body p-4 p-lg-5"><h2 class="h3 mb-4">Contact our travel team</h2>@if($errors->any())<div class="alert alert-danger" role="alert">Please review the highlighted fields.</div>@endif<form method="POST" action="{{ route('contact.store') }}" aria-label="Contact enquiry form">@csrf<div class="honeypot" aria-hidden="true"><label for="website">Website</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div><div class="row"><div class="col-md-6"><x-public.form-field name="name" label="Name" required /></div><div class="col-md-6"><x-public.form-field name="email" label="Email" type="email" required /></div><div class="col-md-6"><x-public.form-field name="phone" label="Phone" type="tel" /></div><div class="col-md-6"><x-public.form-field name="country" label="Country" /></div></div><x-public.form-field name="subject" label="Subject" :value="request('package') ? 'Package enquiry: '.str(request('package'))->replace('-', ' ')->title() : null" /><x-public.textarea name="message" label="Message" rows="6" required /><button class="btn btn-forest px-4 py-3" type="submit">Send message</button></form></div></div></div>
    </div></div></section>

    @if($websiteSettings?->google_maps_embed_url)<section class="contact-map-section"><iframe title="Sri Soul Ventures location" src="{{ $websiteSettings->google_maps_embed_url }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></section>@endif

    <section class="section-space"><div class="container"><x-public.section-heading :eyebrow="$faqSection?->subheading ?: 'Helpful answers'" :title="$faqSection?->heading ?: 'Frequently asked questions'" :subtitle="$faqSection?->content" />@if($faqs->isNotEmpty())<div class="accordion contact-faq col-lg-9 mx-auto" id="contactFaq">@foreach($faqs as $faq)<div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#contactFaq{{ $faq->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">{{ $faq->question }}</button></h3><div id="contactFaq{{ $faq->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#contactFaq"><div class="accordion-body">{!! nl2br(e($faq->answer)) !!}</div></div></div>@endforeach</div>@else<x-public.empty-state title="Frequently asked questions are being updated" />@endif</div></section>
@endsection
