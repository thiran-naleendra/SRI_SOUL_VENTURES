@extends('layouts.admin', ['title' => 'Destinations', 'description' => 'Manage destination content, media, and publishing.'])

@section('page-actions')
    @can('destinations.create')
        <a href="{{ route('admin.destinations.create') }}" class="btn btn-admin-primary">Add destination</a>
    @endcan
@endsection

@section('content')
    <div class="card admin-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-4"><label class="form-label" for="search">Search</label><input class="form-control"
                        id="search" name="search" value="{{ request('search') }}" placeholder="Name or slug"></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="region">Region</label><select
                        class="form-select" id="region" name="region">
                        <option value="">All regions</option>@foreach($regions as $region)
                            <option value="{{ $region->id }}" @selected((string) request('region') === (string) $region->id)>
                        {{ $region->name }}</option>@endforeach
                    </select></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="featured">Featured</label><select
                        class="form-select" id="featured" name="featured">
                        <option value="">All</option>
                        <option value="yes" @selected(request('featured') === 'yes')>Featured</option>
                        <option value="no" @selected(request('featured') === 'no')>Not featured</option>
                    </select></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="status">Status</label><select
                        class="form-select" id="status" name="status">
                        <option value="">All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        <option value="trashed" @selected(request('status') === 'trashed')>Trashed</option>
                    </select></div>
                <div class="col-sm-6 col-lg-2 d-flex gap-2"><button
                        class="btn btn-admin-primary flex-grow-1">Filter</button><a class="btn btn-outline-secondary"
                        href="{{ route('admin.destinations.index') }}">Reset</a></div>
            </form>
        </div>
    </div>

    <div class="card admin-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Destination</th>
                            <th>Region</th>
                            <th>Order</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($destinations as $destination)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($destination->cover_image)<img
                                            src="{{ Storage::disk('public')->url($destination->cover_image) }}" alt=""
                                        class="rounded object-fit-cover" width="64" height="48">@else<div
                                            class="image-preview" style="width:64px;min-height:48px">▧</div>@endif
                                        <div><strong>{{ $destination->name }}</strong>
                                            <div class="small text-muted">{{ $destination->slug }}</div>
                                            @if($destination->trashed())<small class="text-danger">Deleted
                                            {{ $destination->deleted_at->diffForHumans() }}</small>@endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $destination->region?->name ?: 'Unassigned region' }}</td>
                                <td>{{ $destination->display_order }}</td>
                                <td><span
                                        class="badge rounded-pill {{ $destination->is_featured ? 'text-bg-success' : 'text-bg-light' }}">{{ $destination->is_featured ? 'Featured' : 'Standard' }}</span>
                                </td>
                                <td><span
                                        class="badge rounded-pill {{ $destination->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $destination->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    @if($destination->trashed())
                                        @can('destinations.delete')
                                            <form method="POST" action="{{ route('admin.destinations.restore', $destination->id) }}"
                                                class="d-inline">@csrf @method('PATCH')<button
                                        class="btn btn-sm btn-outline-success">Restore</button></form>@endcan
                                    @else
                                        @can('destinations.update')
                                            <form method="POST" action="{{ route('admin.destinations.toggle', $destination->id) }}"
                                                class="d-inline">@csrf @method('PATCH')<button
                                                    class="btn btn-sm btn-outline-secondary">{{ $destination->is_active ? 'Deactivate' : 'Activate' }}</button>
                                            </form><a class="btn btn-sm btn-outline-primary"
                                        href="{{ route('admin.destinations.edit', $destination->id) }}">Edit</a>@endcan
                                        @can('destinations.delete')<button class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#confirmationModal"
                                            data-confirm-action="{{ route('admin.destinations.destroy', $destination->id) }}"
                                        data-confirm-message="Move {{ $destination->name }} to trash?">Delete</button>@endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="admin-empty m-3">No destinations found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($destinations->hasPages())
        <div class="card-footer bg-white border-0">{{ $destinations->links('pagination::bootstrap-5') }}</div>@endif
    </div>
@endsection