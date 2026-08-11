<?php

namespace App\Providers;

use App\Support\EnvironmentValidator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExceptionHandler::class, \App\Exceptions\Handler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimits();

        if ($this->app->runningInConsole()) {
            return;
        }

        EnvironmentValidator::validate();
    }

    /**
     * Configure named rate limiters for auth endpoints.
     *
     * Keys are scoped per account (email) and IP so a distributed brute-force
     * against one account is bounded, and hitting one endpoint does not
     * exhaust the budget of another.
     */
    protected function configureRateLimits(): void
    {
        RateLimiter::for('auth.login', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::lower((string) $request->input('email', 'guest')).'|'.$request->ip()
            );
        });

        RateLimiter::for('auth.register', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::lower((string) $request->input('email', 'guest')).'|'.$request->ip()
            );
        });

        RateLimiter::for('auth.forgot', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::lower((string) $request->input('email', 'guest')).'|'.$request->ip()
            );
        });

        RateLimiter::for('auth.reset', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::lower((string) $request->input('email', 'guest')).'|'.$request->ip()
            );
        });
    }
}
