@php($authSettings = App\Models\WebsiteSetting::current())
<x-guest-layout>
    <div class="admin-login-page">
        <section class="admin-login-brand" aria-label="Sri Soul Ventures introduction">
            <a class="admin-login-home" href="{{ route('home') }}" aria-label="Return to Sri Soul Ventures home">
                @if($authSettings?->logo)
                    <img src="{{ Storage::disk('public')->url($authSettings->logo) }}" alt="{{ $authSettings->website_name }} logo">
                @else
                    <span class="admin-login-logo-mark">SS</span>
                    <span>{{ $authSettings?->website_name ?: 'Sri Soul Ventures' }}</span>
                @endif
            </a>
            <div class="admin-login-brand-content">
                <p class="admin-login-kicker">Welcome back</p>
                <h1>Manage journeys that reveal the soul of Sri Lanka.</h1>
                <p>Access destinations, experiences, packages and traveller enquiries from one secure workspace.</p>
                <div class="admin-login-features" aria-label="Administration features">
                    <span>Thoughtful content</span><span>Local expertise</span><span>Secure access</span>
                </div>
            </div>
            <p class="admin-login-brand-footer">Sri Lanka, designed with care.</p>
        </section>

        <main class="admin-login-main">
            <div class="admin-login-card">
                <div class="admin-login-mobile-brand">
                    <span class="admin-login-logo-mark">SS</span>
                    <span>{{ $authSettings?->website_name ?: 'Sri Soul Ventures' }}</span>
                </div>
                <p class="admin-login-eyebrow">Administration portal</p>
                <h2>Sign in to your account</h2>
                <p class="admin-login-intro">Enter your administrator credentials to continue.</p>

                @if(session('status'))<div class="auth-alert auth-alert-success" role="status">{{ session('status') }}</div>@endif
                @if($errors->any())<div class="auth-alert auth-alert-error" role="alert"><strong>We couldn’t sign you in.</strong><span>Please check your details and try again.</span></div>@endif

                <form class="admin-login-form" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="auth-field">
                        <label for="email">Email address</label>
                        <div class="auth-input-wrap">
                            <span aria-hidden="true">✉</span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com" class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
                        </div>
                        @error('email')<p class="auth-field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="auth-field">
                        <div class="auth-label-row"><label for="password">Password</label>@if(Route::has('password.request'))<a href="{{ route('password.request') }}">Forgot password?</a>@endif</div>
                        <div class="auth-input-wrap">
                            <span aria-hidden="true">●</span>
                            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}">
                            <button type="button" class="auth-password-toggle" data-password-toggle aria-controls="password" aria-label="Show password"><span aria-hidden="true">Show</span></button>
                        </div>
                        @error('password')<p class="auth-field-error">{{ $message }}</p>@enderror
                    </div>

                    <label class="auth-remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Keep me signed in on this device</span></label>
                    <button class="admin-login-submit" type="submit">Sign in securely <span aria-hidden="true">→</span></button>
                </form>

                <div class="admin-login-help"><span aria-hidden="true">◈</span><p><strong>Need help accessing your account?</strong><span>Contact your Sri Soul Ventures system administrator.</span></p></div>
                <a class="admin-login-back" href="{{ route('home') }}">← Back to website</a>
            </div>
        </main>
    </div>
</x-guest-layout>
