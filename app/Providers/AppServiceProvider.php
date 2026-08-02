<?php

namespace App\Providers;

use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn (User $user, string $ability): ?bool => $user->hasRole('super_admin') ? true : null);
        View::composer('layouts.public', fn ($view) => $view->with('websiteSettings', WebsiteSetting::current()));
    }
}
