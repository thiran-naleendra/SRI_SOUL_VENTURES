@extends('layouts.admin', ['title' => 'Dashboard', 'description' => 'A live overview of Sri Soul Ventures.'])

@section('content')
    <div class="row g-3 dashboard-stats">
        @isset($destinationStats)
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="Total destinations" :value="$destinationStats->total" tone="forest" /></div>
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="Active destinations" :value="$destinationStats->active" tone="leaf" /></div>
        @endisset
        @isset($experienceStats)
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="Total experiences" :value="$experienceStats->total" tone="gold" /></div>
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="Active experiences" :value="$experienceStats->active" tone="leaf" /></div>
        @endisset
        @isset($packageStats)
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="Total packages" :value="$packageStats->total" tone="forest" /></div>
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="Active packages" :value="$packageStats->active" tone="leaf" /></div>
        @endisset
        @isset($packageEnquiryStats)
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="Total package enquiries" :value="$packageEnquiryStats->total" tone="blue" /></div>
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="New package enquiries" :value="$packageEnquiryStats->new_count" tone="gold" /></div>
        @endisset
        @isset($customTourStats)
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="Total custom tour requests" :value="$customTourStats->total" tone="blue" /></div>
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="New custom tour requests" :value="$customTourStats->new_count" tone="gold" /></div>
        @endisset
        @isset($contactEnquiryStats)
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="Total contact enquiries" :value="$contactEnquiryStats->total" tone="forest" /></div>
            <div class="col-sm-6 col-xl-3"><x-admin.dashboard-stat label="Unread contact enquiries" :value="$contactEnquiryStats->unread" tone="danger" /></div>
        @endisset
    </div>

    @if ($monthlyEnquiries->isNotEmpty())
        @php
            $chartMaximum = max(1, (int) $monthlyEnquiries->max('total'));
        @endphp
        <section class="card admin-card mt-4" aria-labelledby="monthly-enquiries-heading">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><h2 class="h5 mb-1" id="monthly-enquiries-heading">Monthly enquiries</h2><p class="text-secondary small mb-0">Package, custom-tour and contact enquiries available to your role.</p></div><span class="badge text-bg-success">Last 12 months</span></div>
                <div class="dashboard-chart" role="img" aria-label="Monthly enquiry totals for the last 12 months">
                    @foreach ($monthlyEnquiries as $month)
                        <div class="dashboard-chart-column" aria-label="{{ $month['label'] }}: {{ $month['total'] }} enquiries"><span class="dashboard-chart-value">{{ $month['total'] }}</span><div class="dashboard-chart-track"><span style="--chart-height: {{ ($month['total'] / $chartMaximum) * 100 }}%"></span></div><small>{{ $month['label'] }}</small></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div class="row g-4 mt-0">
        @isset($recentPackageEnquiries)
            <div class="col-xl-6"><x-admin.dashboard-table title="Recent package enquiries" :url="route('admin.package-enquiries.index')"><thead><tr><th>Customer</th><th>Package</th><th>Status</th><th>Date</th></tr></thead><tbody>@forelse($recentPackageEnquiries as $enquiry)<tr><td><a href="{{ route('admin.package-enquiries.show', $enquiry) }}">{{ $enquiry->customer_name }}</a><small>{{ $enquiry->email }}</small></td><td>{{ $enquiry->package?->title ?: 'Unavailable package' }}</td><td><span class="badge text-bg-{{ $enquiry->status === 'new' ? 'warning' : 'secondary' }}">{{ str($enquiry->status)->replace('_', ' ')->title() }}</span></td><td>{{ $enquiry->created_at->format('d M') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No package enquiries yet.</td></tr>@endforelse</tbody></x-admin.dashboard-table></div>
        @endisset

        @isset($recentCustomTourRequests)
            <div class="col-xl-6"><x-admin.dashboard-table title="Recent custom tour requests" :url="route('admin.custom-tour-requests.index')"><thead><tr><th>Customer</th><th>Package</th><th>Status</th><th>Date</th></tr></thead><tbody>@forelse($recentCustomTourRequests as $request)<tr><td><a href="{{ route('admin.custom-tour-requests.show', $request) }}">{{ $request->customer_name }}</a><small>{{ $request->email }}</small></td><td>{{ $request->package?->title ?: 'Custom journey' }}</td><td><span class="badge text-bg-{{ $request->status === 'new' ? 'warning' : 'secondary' }}">{{ str($request->status)->replace('_', ' ')->title() }}</span></td><td>{{ $request->created_at->format('d M') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No custom tour requests yet.</td></tr>@endforelse</tbody></x-admin.dashboard-table></div>
        @endisset

        @isset($recentContactEnquiries)
            <div class="col-xl-6"><x-admin.dashboard-table title="Recent contact enquiries" :url="route('admin.contact-enquiries.index')"><thead><tr><th>Sender</th><th>Subject</th><th>Read</th><th>Date</th></tr></thead><tbody>@forelse($recentContactEnquiries as $enquiry)<tr><td><a href="{{ route('admin.contact-enquiries.show', $enquiry) }}">{{ $enquiry->name }}</a><small>{{ $enquiry->email }}</small></td><td>{{ Str::limit($enquiry->subject ?: 'General enquiry', 35) }}</td><td><span class="badge text-bg-{{ $enquiry->is_read ? 'secondary' : 'danger' }}">{{ $enquiry->is_read ? 'Read' : 'Unread' }}</span></td><td>{{ $enquiry->created_at->format('d M') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No contact enquiries yet.</td></tr>@endforelse</tbody></x-admin.dashboard-table></div>
        @endisset

        @isset($popularPackages)
            <div class="col-xl-6"><x-admin.dashboard-table title="Popular packages" :url="route('admin.packages.index', ['popular' => 1])"><thead><tr><th>Package</th><th>Category</th><th>Duration</th><th>Price</th></tr></thead><tbody>@forelse($popularPackages as $package)<tr><td><a href="{{ route('admin.packages.edit', $package) }}">{{ $package->title }}</a></td><td>{{ $package->category?->name ?: 'Uncategorized' }}</td><td>{{ $package->days }} days</td><td>{{ $package->starting_price !== null ? $package->currency.' '.number_format((float) $package->starting_price, 2) : '—' }}</td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No popular packages.</td></tr>@endforelse</tbody></x-admin.dashboard-table></div>
        @endisset

        @isset($popularExperiences)
            <div class="col-xl-6"><x-admin.dashboard-table title="Popular experiences" :url="route('admin.experiences.index', ['featured' => 1])"><thead><tr><th>Experience</th><th>Destination</th><th>Category</th><th>Price</th></tr></thead><tbody>@forelse($popularExperiences as $experience)<tr><td><a href="{{ route('admin.experiences.edit', $experience) }}">{{ $experience->title }}</a></td><td>{{ $experience->destination?->name ?: 'Unavailable destination' }}</td><td>{{ $experience->category?->name ?: 'Uncategorized' }}</td><td>{{ $experience->starting_price !== null ? $experience->currency.' '.number_format((float) $experience->starting_price, 2) : '—' }}</td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No popular experiences.</td></tr>@endforelse</tbody></x-admin.dashboard-table></div>
        @endisset
    </div>
@endsection
