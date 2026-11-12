<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToHttps
{
    /**
     * Impose HTTPS : une requête arrivée en clair est renvoyée vers la même adresse chiffrée.
     * Les formulaires (POST…) reçoivent un 308 pour que le navigateur renvoie les données telles quelles.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.force_https') && ! $request->isSecure()) {
            return redirect()->secure($request->getRequestUri(), $request->isMethodSafe() ? 301 : 308);
        }

        return $next($request);
    }
}
