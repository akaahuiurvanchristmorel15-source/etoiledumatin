<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Ajoute les en-têtes HTTP de sécurité à chaque réponse :
     * politique de sécurité du contenu (CSP), anti-clickjacking, anti-sniffing MIME,
     * politique de référent, permissions navigateur et HSTS en HTTPS.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jeton à usage unique : seuls les scripts inline qui le portent (nonce="{{ Vite::cspNonce() }}") s'exécutent
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(self)');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        // Pages HTML seulement : une CSP sur un PDF bloquerait la visionneuse intégrée du navigateur
        if (str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy($nonce, $request->isSecure()));
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Sources autorisées : le site lui-même, Google Fonts, et Paystack / Google comme destinations de formulaires.
     * 'unsafe-eval' reste nécessaire à Alpine.js (expressions x-data, @click…) sur le site public.
     */
    private function contentSecurityPolicy(string $nonce, bool $isSecure): string
    {
        $devServer = Vite::isRunningHot() ? rtrim((string) file_get_contents(public_path('hot'))) : '';
        $devSocket = $devServer !== '' ? preg_replace('#^http#', 'ws', $devServer) : '';

        $directives = [
            'default-src' => "'self'",
            'script-src' => trim("'self' 'nonce-{$nonce}' 'unsafe-eval' {$devServer}"),
            'style-src' => trim("'self' 'unsafe-inline' https://fonts.googleapis.com {$devServer}"),
            'font-src' => "'self' https://fonts.gstatic.com data:",
            'img-src' => "'self' data: blob: https:",
            'media-src' => "'self' blob:",
            'connect-src' => trim("'self' {$devServer} {$devSocket}"),
            'frame-src' => "'self'",
            'worker-src' => "'self' blob:",
            'object-src' => "'none'",
            'base-uri' => "'self'",
            'form-action' => "'self' https://checkout.paystack.com https://accounts.google.com",
            'frame-ancestors' => "'self'",
        ];

        $policy = collect($directives)->map(fn (string $sources, string $name): string => "{$name} {$sources}")->implode('; ');

        return $isSecure ? $policy.'; upgrade-insecure-requests' : $policy;
    }
}
