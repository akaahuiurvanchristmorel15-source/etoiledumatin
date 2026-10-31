<?php

use App\Http\Middleware\BlockVisitorsDuringMaintenance;
use App\Http\Middleware\EnsureAccountEmailIsVerified;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\IgnoreArrayQueryParameters;
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

        // Le site est servi derrière Cloudflare : on lit l'IP réelle du visiteur et le protocole (HTTPS)
        // dans les en-têtes X-Forwarded-*, uniquement s'ils viennent des serveurs de Cloudflare.
        // Plages officielles : https://www.cloudflare.com/ips/ (à mettre à jour si Cloudflare les modifie).
        $middleware->trustProxies(
            at: [
                '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
                '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
                '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
                '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
                '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
                '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
            ],
            headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->web(append: [
            IgnoreArrayQueryParameters::class,
            SecurityHeaders::class,
            'throttle:global',
            BlockVisitorsDuringMaintenance::class,
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
