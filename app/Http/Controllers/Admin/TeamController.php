<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TeamController extends Controller
{
    /**
     * Rôles attribuables depuis le back-office (le super administrateur n'est pas attribuable).
     *
     * @var array<string, string>
     */
    public const ASSIGNABLE_ROLES = [
        'admin' => 'Administrateur',
        'manager' => 'Gestionnaire catalogue',
        'logistics' => 'Logistique',
    ];

    public function index(): View
    {
        $members = User::whereIn('role', ['super_admin', ...array_keys(self::ASSIGNABLE_ROLES)])
            ->orderBy('name')
            ->get();

        return view('admin.settings.team', [
            'members' => $members,
            'roles' => self::ASSIGNABLE_ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('createMember', [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(self::ASSIGNABLE_ROLES))],
            'password' => ['required', Password::min(8)],
        ]);

        $member = User::create($validated);

        AuditLog::log($request->user()->id, 'team_member_created', ['user_id' => $member->id, 'role' => $member->role]);

        return back()->with('success', "{$member->name} a rejoint l'équipe.");
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        $this->ensureMemberCanBeManaged($request, $member);

        $validated = $request->validate([
            'role' => ['required', Rule::in(array_keys(self::ASSIGNABLE_ROLES))],
        ]);

        $member->update($validated);

        AuditLog::log($request->user()->id, 'team_member_role_updated', ['user_id' => $member->id, 'role' => $member->role]);

        return back()->with('success', "Le rôle de {$member->name} a été mis à jour.");
    }

    public function destroy(Request $request, User $member): RedirectResponse
    {
        $this->ensureMemberCanBeManaged($request, $member);

        // Le compte est conservé (historique des commandes) mais perd l'accès au back-office.
        $member->update(['role' => 'customer']);

        AuditLog::log($request->user()->id, 'team_member_revoked', ['user_id' => $member->id]);

        return back()->with('success', "{$member->name} n'a plus accès à l'administration.");
    }

    protected function ensureMemberCanBeManaged(Request $request, User $member): void
    {
        abort_if($member->is($request->user()), 403, 'Vous ne pouvez pas modifier votre propre rôle.');
        abort_if($member->role === 'super_admin', 403, 'Le super administrateur ne peut pas être modifié.');
        abort_unless($member->isStaff(), 404);
    }
}
