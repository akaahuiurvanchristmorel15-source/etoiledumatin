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
            return redirect()->guest(route('admin.login'));
        }

        if (! auth()->user()->isStaff()) {
            abort(403, 'Accès réservé au personnel d\'administration de NSOROMMA.');
        }

        return $next($request);
    }
}
