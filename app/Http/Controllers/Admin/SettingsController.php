<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\BlockVisitorsDuringMaintenance;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function profile(Request $request): View
    {
        return view('admin.settings.profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:25'],
        ]);

        $user->update($validated);

        AuditLog::log($user->id, 'profile_updated');

        return back()->with('success', 'Vos informations ont été enregistrées.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        AuditLog::log($request->user()->id, 'password_changed');

        return back()->with('success', 'Votre mot de passe a été modifié.');
    }

    public function shipping(): View
    {
        Gate::authorize('manage-store');

        return view('admin.settings.shipping', ['zones' => config('shipping.zones')]);
    }

    public function updateShipping(Request $request): RedirectResponse
    {
        Gate::authorize('manage-store');

        $zoneKeys = array_keys(config('shipping.zones'));

        $rules = [];
        foreach ($zoneKeys as $zone) {
            $rules["zones.{$zone}.price"] = ['required', 'integer', 'min:0', 'max:1000000'];
            $rules["zones.{$zone}.delay"] = ['required', 'string', 'max:60'];
        }

        $validated = $request->validate($rules, [], ['zones.*.price' => 'tarif', 'zones.*.delay' => 'délai']);

        foreach ($zoneKeys as $zone) {
            Setting::put("shipping.{$zone}.price", (string) $validated['zones'][$zone]['price']);
            Setting::put("shipping.{$zone}.delay", $validated['zones'][$zone]['delay']);
        }

        AuditLog::log($request->user()->id, 'shipping_settings_updated');

        return back()->with('success', 'Les frais de livraison ont été mis à jour.');
    }

    public function content(): View
    {
        Gate::authorize('manage-store');

        return view('admin.settings.content', [
            'fields' => collect(config('site.content'))->groupBy('group', preserveKeys: true),
        ]);
    }

    public function updateContent(Request $request): RedirectResponse
    {
        Gate::authorize('manage-store');

        $fields = config('site.content');

        $validated = $request->validate(
            collect($fields)->map(fn (array $field) => $field['rules'])->all(),
            [],
            collect($fields)->map(fn (array $field) => mb_strtolower($field['label']))->all()
        );

        foreach (array_keys($fields) as $name) {
            Setting::put('content.'.$name, trim((string) ($validated[$name] ?? '')));
        }

        AuditLog::log($request->user()->id, 'site_content_updated');

        return back()->with('success', 'Les contenus du site ont été mis à jour.');
    }

    public function maintenance(): View
    {
        Gate::authorize('manage-store');

        return view('admin.settings.maintenance', [
            'isActive' => BlockVisitorsDuringMaintenance::isActive(),
            'maintenanceMessage' => Setting::get('maintenance.message'),
        ]);
    }

    /**
     * Active ou désactive le mode maintenance : seuls l'équipe et la connexion d'administration restent accessibles.
     */
    public function updateMaintenance(Request $request): RedirectResponse
    {
        Gate::authorize('manage-store');

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $isEnabled = (bool) $validated['enabled'];

        Setting::put('maintenance.enabled', $isEnabled ? '1' : '0');
        Setting::put('maintenance.message', trim((string) ($validated['message'] ?? '')));

        AuditLog::log($request->user()->id, $isEnabled ? 'maintenance_enabled' : 'maintenance_disabled');

        return back()->with('success', $isEnabled
            ? 'Mode maintenance activé : les visiteurs voient la page « Le site sera bientôt disponible ».'
            : 'Mode maintenance désactivé : le site est de nouveau ouvert à tous.');
    }
}
