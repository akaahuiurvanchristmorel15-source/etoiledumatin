<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\AdminLoginCode;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Double authentification de l'équipe : après le mot de passe, un code à 6 chiffres envoyé par e-mail.
 * Tant que le code n'est pas saisi, le membre n'est pas connecté : la session ne garde que sa demande en attente.
 */
class AdminTwoFactorController extends Controller
{
    public const SESSION_KEY = 'admin_two_factor';

    public const TRUSTED_DEVICE_COOKIE = 'nsoromma_admin_device';

    public const CODE_VALID_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public const TRUSTED_DEVICE_DAYS = 30;

    /**
     * Un membre de l'équipe vient de saisir le bon mot de passe : faut-il encore lui demander un code ?
     */
    public static function isRequiredFor(Request $request, User $user): bool
    {
        if (! config('auth.admin_two_factor') || ! $user->isStaff()) {
            return false;
        }

        $trustedDevice = (string) $request->cookie(self::TRUSTED_DEVICE_COOKIE);

        return ! hash_equals(self::deviceFingerprint($user), $trustedDevice);
    }

    /**
     * Met la connexion en attente et envoie le code. Le membre est déconnecté jusqu'à sa saisie.
     */
    public static function start(Request $request, User $user, bool $remember): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->regenerate();

        $request->session()->put(self::SESSION_KEY, [
            'user_id' => $user->id,
            'remember' => $remember,
        ]);

        if (! self::sendCode($request, $user)) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('admin.login')->withErrors([
                'email' => 'Le code de connexion n\'a pas pu être envoyé par e-mail. Réessayez dans un instant ou contactez l\'administrateur du site.',
            ]);
        }

        AuditLog::log($user->id, 'two_factor_code_sent');

        return redirect()->route('admin.two-factor');
    }

    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor', ['maskedEmail' => $this->maskEmail($user->email)]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Votre demande de connexion a expiré. Saisissez à nouveau vos identifiants.']);
        }

        $request->merge(['code' => preg_replace('/\D/', '', (string) $request->input('code'))]);
        $request->validate(['code' => ['required', 'digits:6']], [], ['code' => 'code']);

        $pending = $request->session()->get(self::SESSION_KEY);

        if (now()->timestamp > ($pending['expires_at'] ?? 0)) {
            throw ValidationException::withMessages(['code' => 'Ce code a expiré. Demandez-en un nouveau.']);
        }

        if (! Hash::check($request->input('code'), $pending['code_hash'] ?? '')) {
            $attempts = ($pending['attempts'] ?? 0) + 1;
            AuditLog::log($user->id, 'two_factor_failed');

            if ($attempts >= self::MAX_ATTEMPTS) {
                $request->session()->forget(self::SESSION_KEY);

                return redirect()->route('admin.login')->withErrors(['email' => 'Trop de codes erronés. Reconnectez-vous pour recevoir un nouveau code.']);
            }

            $request->session()->put(self::SESSION_KEY.'.attempts', $attempts);

            throw ValidationException::withMessages(['code' => 'Code incorrect. Il vous reste '.(self::MAX_ATTEMPTS - $attempts).' essai(s).']);
        }

        $request->session()->forget(self::SESSION_KEY);
        Auth::login($user, (bool) ($pending['remember'] ?? false));
        $request->session()->regenerate();

        if ($request->boolean('trust_device')) {
            Cookie::queue(self::TRUSTED_DEVICE_COOKIE, self::deviceFingerprint($user), self::TRUSTED_DEVICE_DAYS * 24 * 60);
        }

        AuditLog::log($user->id, 'user_logged_in');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if (! self::sendCode($request, $user)) {
            return back()->withErrors(['code' => 'Le code n\'a pas pu être envoyé. Réessayez dans un instant.']);
        }

        return back()->with('success', 'Un nouveau code vient d\'être envoyé.');
    }

    /**
     * Génère un nouveau code (seule son empreinte est gardée en session) et l'envoie.
     */
    private static function sendCode(Request $request, User $user): bool
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put(self::SESSION_KEY.'.code_hash', Hash::make($code));
        $request->session()->put(self::SESSION_KEY.'.expires_at', now()->addMinutes(self::CODE_VALID_MINUTES)->timestamp);
        $request->session()->put(self::SESSION_KEY.'.attempts', 0);

        try {
            $user->notify(new AdminLoginCode($code, self::CODE_VALID_MINUTES));
        } catch (Throwable $exception) {
            Log::error('Envoi du code de double authentification impossible', ['user_id' => $user->id, 'error' => $exception->getMessage()]);

            return false;
        }

        return true;
    }

    /**
     * Empreinte de l'appareil reconnu : liée au compte et à son mot de passe (un changement de mot de passe l'annule).
     */
    private static function deviceFingerprint(User $user): string
    {
        return hash_hmac('sha256', $user->id.'|'.$user->password, (string) config('app.key'));
    }

    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get(self::SESSION_KEY.'.user_id');
        $user = $userId ? User::find($userId) : null;

        return $user?->isStaff() ? $user : null;
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($name, 0, 2).str_repeat('•', max(mb_strlen($name) - 2, 1)).'@'.$domain;
    }
}
