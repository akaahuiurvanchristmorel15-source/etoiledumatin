<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * La connexion Google n'est proposée que si les identifiants OAuth sont configurés.
     */
    public static function isEnabled(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    public function redirect(): SymfonyRedirectResponse|RedirectResponse
    {
        abort_unless(self::isEnabled(), 404);

        return Socialite::driver('google')->scopes(['openid', 'profile', 'email'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless(self::isEnabled(), 404);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            Log::warning('Connexion Google échouée', ['message' => $exception->getMessage()]);

            return redirect()->route('login')->with('error', 'La connexion avec Google a échoué ou a été annulée. Veuillez réessayer.');
        }

        $email = Str::lower((string) $googleUser->getEmail());
        $emailIsVerified = (bool) data_get($googleUser->user, 'email_verified', data_get($googleUser->user, 'verified_email', false));

        if ($email === '' || ! $emailIsVerified) {
            return redirect()->route('login')->with('error', 'Votre compte Google doit avoir une adresse e-mail vérifiée.');
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            // Compte existant : on le relie à Google (l'adresse est vérifiée par Google)
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
            $action = 'user_logged_in_google';
        } else {
            $user = User::create([
                'name' => Str::limit(strip_tags((string) ($googleUser->getName() ?: Str::before($email, '@'))), 120, ''),
                'email' => $email,
                'password' => Str::password(40),
            ]);
            $user->forceFill(['google_id' => $googleUser->getId(), 'email_verified_at' => now()])->save();
            $action = 'user_registered_google';
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        AuditLog::log($user->id, $action);

        return AuthController::redirectAfterLogin($request, remember: true);
    }
}
