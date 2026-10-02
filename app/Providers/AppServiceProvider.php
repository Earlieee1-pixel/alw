<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     * I-define ang rate limiters para sa security.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * I-configure ang rate limiters para sa tanan nga sensitive routes.
     */
    private function configureRateLimiting(): void
    {
        // Login — 5 attempts per 10 minutes per IP
        // Para mapugong ang brute force attacks
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinutes(10, 5)
                ->by($request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'email' => 'Too many login attempts. Your access has been temporarily locked for 10 minutes.',
                    ]);
                });
        });

        // Register — 3 attempts per minute per IP
        // Para dili ma-spam ang registration
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->ip());
        });

        // Message send — 30 messages per minute per user
        // Para dili ma-spam ang messaging
        RateLimiter::for('messages', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->user()?->id ?? $request->ip());
        });

        // Message poll — 30 polls per minute per user
        // Gi-call every 3 seconds = 20/min, 30 gives a buffer
        RateLimiter::for('poll', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->user()?->id ?? $request->ip());
        });

        // Notifications recent — 60 per minute per user
        // Gi-call on bell click, dili kailangan strict kaayo
        RateLimiter::for('notifications', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?? $request->ip());
        });
    }
}
