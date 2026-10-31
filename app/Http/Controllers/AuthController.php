<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Nombre d'essais de connexion autorisés avant blocage temporaire (par e-mail et adresse IP).
     */
    protected const MAX_LOGIN_ATTEMPTS = 5;

    public function showLogin(): View
    {
        return view('pages.auth.login');
    }

    public function showAdminLogin(): View|RedirectResponse
    {
        if (Auth::user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $throttleKey = $this->loginThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AuditLog::log(null, 'login_locked', ['email' => $credentials['email']]);

            throw ValidationException::withMessages([
                'email' => "Trop de tentatives de connexion. Réessayez dans {$seconds} secondes.",
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);
            AuditLog::log(null, 'login_failed', ['email' => $credentials['email']]);

            throw ValidationException::withMessages([
                'email' => 'Ces identifiants ne correspondent à aucun compte.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        AuditLog::log(Auth::id(), 'user_logged_in');

        return $this->redirectAfterLogin($request, $request->boolean('remember'));
    }

    public function showRegister(): View
    {
        return view('pages.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'name' => trim(strip_tags((string) $request->input('name'))),
        ]);

        $validated = $request->validate([
            // Champ invisible : seuls les robots le remplissent
            'website' => ['prohibited'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9 ().-]{8,20}$/'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ], [
            'website.prohibited' => 'Votre inscription n\'a pas pu être validée.',
            'phone.regex' => 'Saisissez un numéro de téléphone valide (8 à 20 chiffres).',
            'terms.accepted' => 'Vous devez accepter les conditions d\'utilisation.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        AuditLog::log($user->id, 'user_registered');

        return redirect()->route('verification.notice')->with('success', 'Votre compte a été créé. Confirmez votre adresse e-mail pour le sécuriser.');
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLog::log(Auth::id(), 'user_logged_out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Vous avez été déconnecté.');
    }

    /**
     * Redirection après connexion : l'administration pour l'équipe (après la double authentification), la bibliothèque pour les lecteurs.
     */
    public static function redirectAfterLogin(Request $request, bool $remember = false): RedirectResponse
    {
        // Équipe : un code reçu par e-mail est demandé avant d'ouvrir l'administration
        if (AdminTwoFactorController::isRequiredFor($request, Auth::user())) {
            return AdminTwoFactorController::start($request, Auth::user(), $remember);
        }

        if (Auth::user()->isStaff()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Un lecteur ne doit pas être renvoyé vers une page d'administration qui lui est interdite
        if (str_contains((string) $request->session()->get('url.intended'), '/admin')) {
            $request->session()->forget('url.intended');
        }

        return redirect()->intended(route('library.index'))->with('success', 'Bienvenue sur votre espace.');
    }

    protected function loginThrottleKey(Request $request): string
    {
        return 'login|'.Str::lower((string) $request->input('email')).'|'.$request->ip();
    }
}
