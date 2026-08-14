<?php

namespace App\Providers;

use App\Exceptions\Handler;
use App\Models\Entreprise;
use App\Policies\Api\EntreprisePolicy;
use App\Support\EnvironmentValidator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimits();
        $this->registerApiBindings();

        if ($this->app->runningInConsole()) {
            return;
        }

        EnvironmentValidator::validate();
    }

    protected function registerApiBindings(): void
    {
        Gate::policy(Entreprise::class, EntreprisePolicy::class);

        Route::bind('entreprise', fn (string $value): Entreprise => Entreprise::where('siren', $value)->firstOrFail());
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
