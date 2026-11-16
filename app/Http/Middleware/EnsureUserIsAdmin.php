<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    private const LAST_ACTIVITY_KEY = 'admin_last_activity';

    /**
     * Requêtes automatiques de l'interface (compteur de commandes) : elles ne prolongent pas la session.
     */
    private const BACKGROUND_ROUTES = ['admin.notifications.orders'];

    /**
     * Réserve l'administration à l'équipe, depuis une adresse autorisée, et la referme après une période d'inactivité.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->guest(route('admin.login'));
        }

        if (! auth()->user()->isStaff()) {
            AuditLog::logThrottled((string) auth()->id(), 900, auth()->id(), 'admin_access_denied', ['path' => '/'.ltrim($request->path(), '/')]);

            abort(403, 'Accès réservé au personnel d\'administration de NSOROMMA.');
        }

        $allowedIps = config('admin.allowed_ips');

        if ($allowedIps !== [] && ! IpUtils::checkIp((string) $request->ip(), $allowedIps)) {
            AuditLog::logThrottled((string) $request->ip(), 900, auth()->id(), 'admin_ip_blocked');

            abort(403, 'L\'administration n\'est pas accessible depuis cette adresse.');
        }

        if ($this->isIdle($request)) {
            AuditLog::log(auth()->id(), 'admin_idle_logout');
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('info', 'Vous avez été déconnecté après '.config('admin.idle_minutes').' minutes d\'inactivité.');
        }

        if (! in_array(Route::currentRouteName(), self::BACKGROUND_ROUTES, true)) {
            $request->session()->put(self::LAST_ACTIVITY_KEY, now()->timestamp);
        }

        return $next($request);
    }

    private function isIdle(Request $request): bool
    {
        $lastActivity = $request->session()->get(self::LAST_ACTIVITY_KEY);

        return $lastActivity !== null && now()->timestamp - $lastActivity > config('admin.idle_minutes') * 60;
    }
}
