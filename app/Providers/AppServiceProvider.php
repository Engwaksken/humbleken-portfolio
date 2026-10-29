<?php

namespace App\Providers;

use App\View\Composers\SiteComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        View::composer([
            'components.layouts.site',
            'projects.index', 'projects.show',
            'articles.index', 'articles.show',
            'auth.login', 'auth.register', 'auth.forgot-password', 'auth.reset-password', 'auth.verify-email', 'auth.confirm-password',
            'account.dashboard', 'account.tickets.index', 'account.tickets.create', 'account.tickets.show',
            'welcome',
        ], SiteComposer::class);

        RateLimiter::for('enquiries', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('tickets', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
    }
}
