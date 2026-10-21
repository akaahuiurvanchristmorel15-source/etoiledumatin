<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $request->validate(['email' => ['required', 'email', 'max:150']]);

        Password::sendResetLink($request->only('email'));

        // Même message que le compte existe ou non : on ne révèle pas quelles adresses sont inscrites
        return back()->with('success', 'Si un compte existe pour cette adresse, un lien de réinitialisation vient de lui être envoyé.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('pages.auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset($validated, function (User $user, string $password): void {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();

            event(new PasswordReset($user));
            AuditLog::log($user->id, 'password_reset');
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Ce lien de réinitialisation est invalide ou a expiré. Faites une nouvelle demande.',
            ]);
        }

        return redirect()->route('login')->with('success', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');
    }
}
