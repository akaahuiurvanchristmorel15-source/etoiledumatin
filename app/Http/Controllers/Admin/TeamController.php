<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class TeamController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.team', [
            'members' => User::where('role', '!=', 'customer')->whereHas('roleModel')->with('roleModel')->orderBy('name')->get(),
            'roles' => $this->assignableRoles(),
        ]);
    }

    /**
     * Ajoute un membre : crée son compte, ou donne accès à l'administration au compte lecteur qui utilise déjà cette adresse.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $validated = $request->validateWithBag('createMember', [
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150'],
            'role' => ['required', Rule::in($this->assignableRoles()->pluck('slug'))],
            'password' => ['nullable', 'string', Password::defaults()],
            'send_invite' => ['boolean'],
        ], [], ['name' => 'nom', 'email' => 'e-mail', 'role' => 'rôle', 'password' => 'mot de passe provisoire']);

        // Comparaison sans tenir compte des majuscules : « Awa@Mail.com » et « awa@mail.com » sont le même compte
        $existing = User::whereRaw('lower(email) = ?', [$validated['email']])->first();

        if ($existing?->isStaff()) {
            throw ValidationException::withMessages(['email' => "{$existing->name} fait déjà partie de l'équipe : changez son rôle dans la liste."])->errorBag('createMember');
        }

        if ($existing) {
            $existing->forceFill(['role' => $validated['role'], 'email_verified_at' => $existing->email_verified_at ?? now()])->save();

            AuditLog::log($request->user()->id, 'team_member_promoted', ['user_id' => $existing->id, 'role' => $existing->role]);

            return back()->with('success', "Le compte existant de {$existing->name} a rejoint l'équipe ({$existing->roleLabel()}). Il se connecte avec son mot de passe habituel.");
        }

        $missing = array_filter([
            'name' => blank($validated['name'] ?? null) ? 'Indiquez le nom du nouveau membre.' : null,
            'password' => blank($validated['password'] ?? null) ? 'Choisissez ou générez un mot de passe provisoire.' : null,
        ]);

        if ($missing) {
            throw ValidationException::withMessages($missing)->errorBag('createMember');
        }

        $member = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $member->forceFill(['role' => $validated['role'], 'email_verified_at' => now()])->save();

        AuditLog::log($request->user()->id, 'team_member_created', ['user_id' => $member->id, 'role' => $member->role]);

        $message = "{$member->name} a rejoint l'équipe ({$member->roleLabel()}). Connexion sur la page administration avec son e-mail et le mot de passe provisoire.";

        if ($request->boolean('send_invite')) {
            if ($this->sendPasswordLink($member)) {
                $message .= ' Un e-mail lui a aussi été envoyé pour choisir son propre mot de passe.';
            } else {
                return back()->with('success', $message)->with('error', "L'e-mail d'invitation n'a pas pu être envoyé : communiquez-lui le mot de passe provisoire.");
            }
        }

        return back()->with('success', $message);
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        $this->ensureMemberCanBeManaged($request, $member);

        $validated = $request->validate([
            'role' => ['required', Rule::in($this->assignableRoles()->pluck('slug'))],
        ]);

        $member->forceFill(['role' => $validated['role']])->save();

        AuditLog::log($request->user()->id, 'team_member_role_updated', ['user_id' => $member->id, 'role' => $member->role]);

        return back()->with('success', "{$member->name} est maintenant « {$member->refresh()->roleLabel()} ».");
    }

    public function destroy(Request $request, User $member): RedirectResponse
    {
        $this->ensureMemberCanBeManaged($request, $member);

        // Le compte est conservé (historique des commandes) mais perd l'accès au back-office.
        $member->forceFill(['role' => 'customer'])->save();

        AuditLog::log($request->user()->id, 'team_member_revoked', ['user_id' => $member->id]);

        return back()->with('success', "{$member->name} n'a plus accès à l'administration.");
    }

    /**
     * Rôles attribuables depuis l'équipe (le super administrateur ne l'est pas).
     *
     * @return Collection<int, Role>
     */
    protected function assignableRoles(): Collection
    {
        return Role::assignable()->orderBy('name')->get();
    }

    /**
     * Envoie le lien « choisir mon mot de passe » ; une messagerie mal configurée ne doit pas faire échouer l'ajout.
     */
    protected function sendPasswordLink(User $member): bool
    {
        try {
            return PasswordBroker::sendResetLink(['email' => $member->email]) === PasswordBroker::RESET_LINK_SENT;
        } catch (\Throwable $exception) {
            Log::warning('Invitation équipe non envoyée', ['user_id' => $member->id, 'error' => $exception->getMessage()]);

            return false;
        }
    }

    protected function ensureMemberCanBeManaged(Request $request, User $member): void
    {
        abort_if($member->is($request->user()), 403, 'Vous ne pouvez pas modifier votre propre rôle.');
        abort_if($member->isSuperAdmin(), 403, 'Le super administrateur ne peut pas être modifié.');
        abort_unless($member->isStaff(), 404);
    }
}
