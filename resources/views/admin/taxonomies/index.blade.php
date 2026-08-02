@extends('layouts.admin', ['title' => $title, 'description' => "Manage {$title}."])

@section('page-actions')
    @can($permissionPrefix.'.create')
        <a href="{{ route($routePrefix.'.create') }}" class="btn btn-admin-primary">Add {{ $singular }}</a>
    @endcan
@endsection

@section('content')
    <div class="card admin-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($routePrefix.'.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6"><label for="search" class="form-label">Search</label><input id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name"></div>
                <div class="col-md-3"><label for="status" class="form-label">Status</label><select id="status" name="status" class="form-select"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option><option value="trashed" @selected(request('status') === 'trashed')>Trashed</option></select></div>
                <div class="col-md-3 d-flex gap-2"><button class="btn btn-admin-primary flex-grow-1">Filter</button><a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-secondary">Reset</a></div>
            </form>
        </div>
    </div>

    <div class="card admin-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead><tr><th class="ps-4">Name</th><th>Slug</th><th>Order</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="ps-4"><strong>{{ $item->name }}</strong>@if($item->trashed())<div><small class="text-danger">Deleted {{ $item->deleted_at->diffForHumans() }}</small></div>@endif</td>
                                <td><code>{{ $item->slug }}</code></td>
                                <td>{{ $item->display_order }}</td>
                                <td><x-admin.status-badge :status="$item->is_active ? 'active' : 'inactive'" /></td>
                                <td class="text-end pe-4 text-nowrap">
                                    @if ($item->trashed())
                                        @can($permissionPrefix.'.delete')<form class="d-inline" method="POST" action="{{ route($routePrefix.'.restore', $item->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success">Restore</button></form>@endcan
                                    @else
                                        @can($permissionPrefix.'.update')<form class="d-inline" method="POST" action="{{ route($routePrefix.'.toggle', $item->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary">{{ $item->is_active ? 'Deactivate' : 'Activate' }}</button></form><a href="{{ route($routePrefix.'.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>@endcan
                                        @can($permissionPrefix.'.delete')<button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmationModal" data-confirm-action="{{ route($routePrefix.'.destroy', $item->id) }}" data-confirm-message="Move {{ $item->name }} to trash?">Delete</button>@endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="admin-empty m-3">No {{ strtolower($title) }} found.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($items->hasPages())<div class="card-footer bg-white border-0 py-3">{{ $items->links('pagination::bootstrap-5') }}</div>@endif
    </div>
@endsection
