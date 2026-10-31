<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockVisitorsDuringMaintenance
{
    /**
     * Pages toujours accessibles en maintenance : administration, connexion de l'équipe (et mot de passe oublié) et confirmations Paystack.
     *
     * @var array<int, string>
     */
    private const ALWAYS_OPEN_ROUTES = ['admin.*', 'password.*', 'logout', 'paystack.webhook', 'seo.robots'];

    /**
     * Mode maintenance (Paramètres › Maintenance) : les visiteurs voient « bientôt disponible »,
     * l'équipe connectée continue de voir le site pour vérifier les modifications.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAlwaysOpen($request) || ! self::isActive() || $request->user()?->isStaff()) {
            return $next($request);
        }

        return response()
            ->view('pages.maintenance', ['message' => Setting::get('maintenance.message')], 503)
            ->header('Retry-After', '3600')
            ->header('X-Robots-Tag', 'noindex');
    }

    public static function isActive(): bool
    {
        try {
            return Setting::get('maintenance.enabled') === '1';
        } catch (QueryException) {
            // Base pas encore installée (premier déploiement) : le site reste ouvert
            return false;
        }
    }

    private function isAlwaysOpen(Request $request): bool
    {
        // Le formulaire de connexion (POST /connexion) n'a pas de nom de route
        return $request->routeIs(...self::ALWAYS_OPEN_ROUTES)
            || ($request->isMethod('post') && $request->is('connexion'));
    }
}
