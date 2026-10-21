<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            AuditLog::log(Auth::id(), 'user_logged_in');

            if (Auth::user()->isStaff()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Un lecteur ne doit pas être renvoyé vers une page d'administration qui lui est interdite
            if (str_contains((string) $request->session()->get('url.intended'), '/admin')) {
                $request->session()->forget('url.intended');
            }

            return redirect()->intended(route('library.index'))->with('success', 'Bienvenue sur votre espace.');
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent à aucun compte actif.',
        ])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('pages.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users'],
            'phone' => ['required', 'string', 'max:25'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
        ]);

        Auth::login($user);

        AuditLog::log($user->id, 'user_registered');

        return redirect()->route('library.index')->with('success', 'Votre compte a été créé avec succès !');
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLog::log(Auth::id(), 'user_logged_out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}
