<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\AuditLog;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.roles', [
            'roles' => Role::assignable()->withCount('members')->orderBy('name')->get(),
            'superAdmin' => Role::where('slug', Role::SUPER_ADMIN)->withCount('members')->first(),
            'permissions' => config('permissions'),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create($request->validated());

        AuditLog::log($request->user()->id, 'role_created', ['role' => $role->slug, 'permissions' => $role->permissions]);

        return back()->with('success', "Le rôle « {$role->name} » a été créé. Vous pouvez maintenant l'attribuer dans l'onglet Équipe.");
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403, 'Le rôle de super administrateur ne peut pas être modifié.');

        $validated = $request->validated();

        // Garde-fou : on ne se retire pas à soi-même le droit de gérer l'équipe (on ne pourrait plus revenir en arrière)
        if ($request->user()->role === $role->slug && ! in_array('manage-team', $validated['permissions'], true)) {
            return back()->with('error', 'Vous ne pouvez pas retirer « Équipe et rôles » à votre propre rôle : vous perdriez l\'accès à cette page.');
        }

        $role->update($validated);

        AuditLog::log($request->user()->id, 'role_updated', ['role' => $role->slug, 'permissions' => $role->permissions]);

        return back()->with('success', "Le rôle « {$role->name} » a été mis à jour.");
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403, 'Le rôle de super administrateur ne peut pas être supprimé.');

        if ($membersCount = $role->members()->count()) {
            return back()->with('error', "Le rôle « {$role->name} » est attribué à {$membersCount} ".($membersCount > 1 ? 'membres' : 'membre').' : changez d\'abord leur rôle dans l\'onglet Équipe.');
        }

        $role->delete();

        AuditLog::log($request->user()->id, 'role_deleted', ['role' => $role->slug]);

        return back()->with('success', "Le rôle « {$role->name} » a été supprimé.");
    }
}
