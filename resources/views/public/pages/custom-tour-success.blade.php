@extends('layouts.public', ['title' => 'Your Custom Tour Request Was Sent'])

@section('content')
    <section class="section-space section-cream min-vh-75 d-flex align-items-center">
        <div class="container"><div class="col-lg-7 mx-auto text-center bg-white rounded-4 shadow-sm p-5"><div class="display-3 text-success mb-3" aria-hidden="true">✓</div><h1 class="display-5">Your journey starts here</h1><p class="lead text-secondary">Thank you for sharing your ideas. A Sri Soul Ventures travel specialist will review your request and get in touch with you shortly.</p><div class="d-flex flex-wrap justify-content-center gap-3 mt-4"><a class="btn btn-forest" href="{{ route('home') }}">Return home</a><a class="btn btn-outline-success rounded-pill px-4" href="{{ route('packages.index') }}">Explore packages</a></div></div></div>
    </section>
@endsection
