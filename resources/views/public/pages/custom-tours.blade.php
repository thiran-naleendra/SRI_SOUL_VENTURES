@extends('layouts.public', ['title' => 'Plan a Custom Sri Lanka Tour'])

@section('content')
    @php
        $hero = $sections->get('hero');
        $heroImage = $hero?->image_path ? Storage::disk('public')->url($hero->image_path) : null;
        $intro = $sections->get('intro');
    @endphp

    <x-public.page-hero :title="$hero?->heading ?: 'Design your own Sri Lanka journey'" :subtitle="$hero?->content ?: ($hero?->subheading ?: 'Tell us what you love and our local specialists will create a journey just for you.')" :image="$heroImage" />
    <div class="container py-3"><x-public.breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Custom tours']]" /></div>

    <section class="section-space pt-4">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-4 sticky-lg-top" style="top: 110px">
                    <x-public.section-heading :eyebrow="$intro?->subheading ?: 'Made for you'" :title="$intro?->heading ?: 'A personal itinerary, shaped by local knowledge'" :subtitle="$intro?->content ?: 'Share your interests, preferred pace and travel dates. We will turn them into a thoughtful Sri Lankan adventure.'" :center="false" />
                </div>
                <div class="col-lg-8">
                    <div class="card custom-tour-form-card"><div class="card-body p-4 p-lg-5">
                        <h2 class="h3 mb-4">Start planning your tour</h2>
                        @if ($errors->any())<div class="alert alert-danger" role="alert">Please review the highlighted fields and try again.</div>@endif
                        <form method="POST" action="{{ route('custom-tours.store') }}" aria-label="Custom tour request form">
                            @csrf
                            <div class="honeypot" aria-hidden="true"><label for="website">Website</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                            <x-public.select-field name="package_id" label="Selected package (optional)" :options="['' => 'Design a tour from scratch'] + $packages->pluck('title', 'id')->all()" :selected="$selectedPackage?->id" />
                            <div class="row">
                                <div class="col-md-6"><x-public.form-field name="name" label="Name" required /></div>
                                <div class="col-md-6"><x-public.form-field name="email" label="Email" type="email" required /></div>
                                <div class="col-md-6"><x-public.form-field name="phone" label="Phone" type="tel" /></div>
                                <div class="col-md-6"><x-public.form-field name="whatsapp" label="WhatsApp" type="tel" /></div>
                                <div class="col-md-6"><x-public.form-field name="country" label="Country" /></div>
                                <div class="col-md-3"><x-public.form-field name="arrival_date" label="Arrival date" type="date" :min="now()->toDateString()" required /></div>
                                <div class="col-md-3"><x-public.form-field name="departure_date" label="Departure date" type="date" :min="now()->toDateString()" /></div>
                                <div class="col-6 col-md-3"><x-public.form-field name="adults" label="Adults" type="number" min="1" :value="1" required /></div>
                                <div class="col-6 col-md-3"><x-public.form-field name="children" label="Children" type="number" min="0" :value="0" required /></div>
                            </div>

                            <fieldset class="mb-4"><legend class="h5">Destinations</legend><div class="choice-grid">@forelse($destinations as $destination)<label class="choice-card"><input class="form-check-input" type="checkbox" name="destination_ids[]" value="{{ $destination->id }}" @checked(in_array($destination->id, old('destination_ids', [])))><span>{{ $destination->name }}</span></label>@empty<p class="text-secondary">Destination choices are being updated.</p>@endforelse</div><x-public.validation-error name="destination_ids" /></fieldset>
                            <fieldset class="mb-4"><legend class="h5">Travel styles</legend><div class="choice-grid">@forelse($travelStyles as $style)<label class="choice-card"><input class="form-check-input" type="checkbox" name="travel_style_ids[]" value="{{ $style->id }}" @checked(in_array($style->id, old('travel_style_ids', [])))><span>{{ $style->name }}</span></label>@empty<p class="text-secondary">Travel-style choices are being updated.</p>@endforelse</div><x-public.validation-error name="travel_style_ids" /></fieldset>

                            <div class="row">
                                <div class="col-md-4"><x-public.form-field name="budget_min" label="Budget minimum" type="number" min="0" step="0.01" /></div>
                                <div class="col-md-4"><x-public.form-field name="budget_max" label="Budget maximum" type="number" min="0" step="0.01" /></div>
                                <div class="col-md-4"><x-public.select-field name="currency" label="Currency" :options="['USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'AUD' => 'AUD', 'LKR' => 'LKR']" selected="USD" /></div>
                                <div class="col-md-6"><x-public.form-field name="accommodation_preference" label="Accommodation preference" placeholder="Boutique, luxury, family-friendly…" /></div>
                                <div class="col-md-6"><x-public.form-field name="transport_preference" label="Transport preference" placeholder="Private vehicle, train journeys…" /></div>
                            </div>
                            <x-public.textarea name="special_requirements" label="Special requirements" rows="3" />
                            <x-public.textarea name="message" label="Tell us about your ideal trip" rows="6" />
                            <button class="btn btn-forest px-4 py-3" type="submit">Send my tour request</button>
                        </form>
                    </div></div>
                </div>
            </div>
        </div>
    </section>
@endsection
