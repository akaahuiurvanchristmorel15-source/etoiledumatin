<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\SecurityMonitor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SecurityController extends Controller
{
    /**
     * Journal de sécurité : alertes des dernières 24 h, adresses les plus actives et derniers événements suspects.
     */
    public function index(Request $request, SecurityMonitor $monitor): View
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in(array_keys(AuditLog::SUSPICIOUS_ACTIONS))],
        ]);

        $since = now()->subDay();

        $countsByAction = AuditLog::suspicious()
            ->where('created_at', '>=', $since)
            ->selectRaw('action, count(*) as total')
            ->groupBy('action')
            ->orderByDesc('total')
            ->pluck('total', 'action');

        $topAddresses = AuditLog::suspicious()
            ->where('created_at', '>=', $since)
            ->whereNotNull('ip_address')
            ->selectRaw('ip_address, count(*) as total')
            ->groupBy('ip_address')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'ip_address');

        $events = AuditLog::suspicious()
            ->when($validated['type'] ?? null, fn ($query, string $type) => $query->where('action', $type))
            ->with('user:id,name,email')
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.security.index', [
            'findings' => $monitor->findings($since),
            'countsByAction' => $countsByAction,
            'topAddresses' => $topAddresses,
            'events' => $events,
            'selectedType' => $validated['type'] ?? null,
        ]);
    }
}
