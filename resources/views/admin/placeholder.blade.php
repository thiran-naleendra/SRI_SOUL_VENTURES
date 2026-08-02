@extends('layouts.admin', ['title' => $title, 'description' => $description])

@section('content')
    <div class="card admin-card">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead><tr><th>Item</th><th>Status</th><th>Last updated</th></tr></thead>
                    <tbody><tr><td colspan="3"><div class="admin-empty"><strong>{{ $title }}</strong><div class="mt-1">This module will be implemented in a future phase.</div></div></td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
