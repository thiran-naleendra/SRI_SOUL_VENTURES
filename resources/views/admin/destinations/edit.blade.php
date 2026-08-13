@extends('layouts.admin', ['title' => 'Edit Destination', 'description' => "Update {$destination->name} and its related content."])
@section('content')
    <form method="POST" action="{{ route('admin.destinations.store') }}" enctype="multipart/form-data"
        data-compact-destination-form data-section-save-url="{{ route('admin.destinations.section', $destination) }}">
        @csrf
        <input type="hidden" name="editing_destination_id" value="{{ $destination->id }}">
        @include('admin.destinations.partials.form')
        <div class="d-flex gap-2 mt-4"><a class="btn btn-light" href="{{ route('admin.destinations.index') }}">Back to destinations</a></div>
</form>@endsection
