<?php

use App\Http\Middleware\EnsureAccountEmailIsVerified;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Seul le webhook Paystack (signé HMAC) est exempté de la protection CSRF
        $middleware->validateCsrfTokens(except: [
            'paystack/webhook',
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
            'throttle:global',
        ]);

        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->isStaff() ? route('admin.dashboard') : route('library.index'));

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'verified.account' => EnsureAccountEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
