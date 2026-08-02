@extends('layouts.admin', ['title' => "Edit {$singular}", 'description' => "Update {$item->name}."])

@section('content')
    <div class="card admin-card"><div class="card-body p-4"><form method="POST" action="{{ route($routePrefix.'.update', $item->id) }}">@csrf @method('PUT') @include('admin.taxonomies.partials.form')<div class="d-flex gap-2"><button class="btn btn-admin-primary">Save changes</button><a href="{{ route($routePrefix.'.index') }}" class="btn btn-light">Cancel</a></div></form></div></div>
@endsection
