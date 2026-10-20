<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à l\'espace de gestion.');
        }

        if (! auth()->user()->isStaff()) {
            abort(403, 'Accès réservé au personnel d\'administration de NSOROMMA.');
        }

        return $next($request);
    }
}
