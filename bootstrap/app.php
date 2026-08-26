<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API-only app: unauthenticated requests never redirect to a "login" web
        // route (none exists here) — always fail with a plain 401 instead.
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->alias([
            'resolve.sanctum' => \App\Http\Middleware\ResolveSanctumUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API-only app: every request under /api should get a JSON 401, never a
        // redirect to a "login" named route (which doesn't exist here).
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());
    })->create();
