<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}Sri Soul Ventures Admin</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @stack('styles')
</head>
<body class="admin-body">
    @php
        $navigation = [
            ['Dashboard', 'admin.dashboard', 'admin.dashboard.view', '⌂'],
            ['Destinations', 'admin.destinations.index', 'destinations.view', '◇'],
            ['Destination Regions', 'admin.destination-regions.index', 'destinations.view', '⌖'],
            ['Experiences', 'admin.experiences.index', 'experiences.view', '✦'],
            ['Experience Categories', 'admin.experience-categories.index', 'experiences.view', '▦'],
            ['Travel Styles', 'admin.travel-styles.index', 'experiences.view', '◈'],
            ['Packages', 'admin.packages.index', 'packages.view', '▣'],
            ['Package Categories', 'admin.package-categories.index', 'packages.view', '▤'],
            ['Package Enquiries', 'admin.package-enquiries.index', 'enquiries.view', '✉'],
            ['Custom Tour Requests', 'admin.custom-tour-requests.index', 'custom_tours.view', '✎'],
            ['Contact Enquiries', 'admin.contact-enquiries.index', 'enquiries.view', '◫'],
            ['Testimonials', 'admin.testimonials.index', 'testimonials.manage', '★'],
            ['Team Members', 'admin.team-members.index', 'team.manage', '♙'],
            ['FAQs', 'admin.faqs.index', 'faqs.manage', '?'],
            ['Pages', 'admin.pages.index', 'pages.manage', '▧'],
            ['Website Settings', 'admin.settings.index', 'settings.manage', '⚙'],
            ['Users', 'admin.users.index', 'users.manage', '♟'],
            ['Roles', 'admin.roles.index', 'roles.manage', '⌘'],
        ];
    @endphp

    <aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
        <div class="offcanvas-header admin-brand p-3">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand d-flex align-items-center gap-2 border-0 text-decoration-none p-0" id="adminSidebarLabel">
                <span class="admin-brand-mark">SS</span>
                <span><strong>Sri Soul</strong><small class="d-block opacity-75">Administration</small></span>
            </a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close navigation"></button>
        </div>
        <div class="offcanvas-body d-block p-3 overflow-y-auto">
            <nav class="admin-nav nav flex-column" aria-label="Admin navigation">
                @foreach ($navigation as [$label, $route, $permission, $icon])
                    @can($permission)
                        @php($active = request()->routeIs(str_ends_with($route, '.index') ? str_replace('.index', '.*', $route) : $route))
                        <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ route($route) }}" @if($active) aria-current="page" @endif>
                            <span class="nav-icon" aria-hidden="true">{{ $icon }}</span>{{ $label }}
                        </a>
                    @endcan
                @endforeach
            </nav>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar navbar sticky-top px-3 px-lg-4">
            <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open navigation">☰</button>
            <div class="ms-auto dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="rounded-circle bg-success-subtle text-success-emphasis d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="admin-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    @if (! request()->routeIs('admin.dashboard'))
                        <li class="breadcrumb-item active" aria-current="page">{{ $title ?? 'Page' }}</li>
                    @endif
                </ol>
            </nav>
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                <div><h1 class="page-title h3 mb-1">{{ $title ?? 'Dashboard' }}</h1><p class="text-secondary mb-0">{{ $description ?? '' }}</p></div>
                @yield('page-actions')
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
            @endif

            @yield('content')
        </main>
    </div>

    <x-admin.confirmation-modal />
    @stack('scripts')
</body>
</html>
