@php($authSettings = App\Models\WebsiteSetting::current())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ request()->routeIs('login') ? 'Admin Sign In' : 'Account' }} · {{ $authSettings?->website_name ?: 'Sri Soul Ventures' }}</title>
    @if($authSettings?->favicon)<link rel="icon" href="{{ Storage::disk('public')->url($authSettings->favicon) }}">@endif
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js', 'resources/js/auth.js'])
</head>
<body class="auth-body">
    @if(request()->routeIs('login'))
        {{ $slot }}
    @else
        <div class="auth-default-shell"><a href="{{ route('home') }}" class="auth-default-brand"><span class="admin-login-logo-mark">SS</span><span>{{ $authSettings?->website_name ?: 'Sri Soul Ventures' }}</span></a><div class="auth-default-card">{{ $slot }}</div></div>
    @endif
</body>
</html>
