<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IgnoreArrayQueryParameters
{
    /**
     * Le site n'utilise que des paramètres d'URL simples (recherche, filtres, tri).
     * Une valeur tableau (« ?search[]=… ») n'a donc rien de légitime : elle est écartée
     * avant d'atteindre les requêtes et les vues, qui la feraient échouer.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->query->replace(array_filter($request->query->all(), fn (mixed $value) => ! is_array($value)));

        return $next($request);
    }
}
