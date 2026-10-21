<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountEmailIsVerified
{
    /**
     * Exige une adresse e-mail confirmée pour les comptes connectés.
     * Les visiteurs sans compte (commande invité) ne sont pas concernés.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? abort(403, 'Confirmez votre adresse e-mail pour continuer.')
                : redirect()->route('verification.notice')->with('info', 'Confirmez votre adresse e-mail pour accéder à cette page.');
        }

        return $next($request);
    }
}
