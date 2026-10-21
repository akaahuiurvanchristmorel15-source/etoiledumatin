<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
}
