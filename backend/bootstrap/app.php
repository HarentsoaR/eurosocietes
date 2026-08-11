<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Middleware\Authenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\RequestContext::class,
        ]);

        // This is an API-only app: never redirect unauthenticated requests to a
        // web "login" route (which does not exist). The JSON 401 is produced by
        // the exception handler via isJsonRequest() (api/* paths).
        Authenticate::redirectUsing(fn () => null);
    })
    // Required: registers the ExceptionHandler binding used during boot.
    // Custom rendering lives in App\Exceptions\Handler (bound in AppServiceProvider).
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
