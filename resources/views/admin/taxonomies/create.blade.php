@extends('layouts.admin', ['title' => "Create {$singular}", 'description' => "Add a new {$singular}."])

@section('content')
    <div class="card admin-card"><div class="card-body p-4"><form method="POST" action="{{ route($routePrefix.'.store') }}">@csrf @include('admin.taxonomies.partials.form')<div class="d-flex gap-2"><button class="btn btn-admin-primary">Create {{ $singular }}</button><a href="{{ route($routePrefix.'.index') }}" class="btn btn-light">Cancel</a></div></form></div></div>
@endsection
