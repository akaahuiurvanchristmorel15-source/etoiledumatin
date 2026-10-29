@extends('layouts.admin')
@section('title', 'Paramètres · Rôles et permissions')

@section('content')
    @include('admin.settings._tabs')

    <div class="grid-aside">
        <div class="stack">
            @if($superAdmin)
                <section class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">{{ $superAdmin->name }} <span class="badge badge-accent" style="margin-left: .3rem;">Toutes les permissions</span></h2>
                            <p class="card-subtitle">{{ $superAdmin->description }} · {{ $superAdmin->members_count }} {{ Str::plural('membre', $superAdmin->members_count) }}</p>
                        </div>
                    </div>
                </section>
            @endif

            @foreach($roles as $role)
                @php $bag = 'role'.$role->id; $checked = $errors->hasBag($bag) ? old('permissions', []) : ($role->permissions ?? []); @endphp
                <section class="card" id="role-{{ $role->id }}">
                    <form action="{{ route('admin.settings.roles.update', $role) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-header">
                            <div>
                                <h2 class="card-title">{{ $role->name }}</h2>
                                <p class="card-subtitle">{{ $role->members_count }} {{ Str::plural('membre', $role->members_count) }} · {{ count($role->permissions ?? []) }} / {{ count($permissions) }} permissions</p>
                            </div>
                        </div>
                        <div class="card-body stack" style="gap: 1rem;">
                            <div class="form-grid">
                                <div class="field">
                                    <label class="label" for="role_name_{{ $role->id }}">Nom</label>
                                    <input type="text" id="role_name_{{ $role->id }}" name="name" value="{{ $errors->hasBag($bag) ? old('name') : $role->name }}" maxlength="60" required class="input @error('name', $bag) is-invalid @enderror">
                                    @error('name', $bag)<p class="error">{{ $message }}</p>@enderror
                                </div>
                                <div class="field">
                                    <label class="label" for="role_description_{{ $role->id }}">Description</label>
                                    <input type="text" id="role_description_{{ $role->id }}" name="description" value="{{ $errors->hasBag($bag) ? old('description') : $role->description }}" maxlength="255" class="input">
                                </div>
                            </div>
                            @include('admin.settings._permission-checks', ['prefix' => 'role_'.$role->id, 'checked' => $checked])
                        </div>
                        <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; gap: .75rem;">
                            <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                            <button type="submit" form="delete-role-{{ $role->id }}" class="btn btn-ghost btn-sm" @disabled($role->members_count > 0) title="{{ $role->members_count > 0 ? 'Attribué à des membres' : 'Supprimer ce rôle' }}">
                                <x-admin.icon name="trash" />
                                <span>Supprimer</span>
                            </button>
                        </div>
                    </form>
                    <form id="delete-role-{{ $role->id }}" action="{{ route('admin.settings.roles.destroy', $role) }}" method="POST" data-confirm="Supprimer le rôle « {{ $role->name }} » ?" hidden>
                        @csrf
                        @method('DELETE')
                    </form>
                </section>
            @endforeach
        </div>

        <aside>
            <form action="{{ route('admin.settings.roles.store') }}" method="POST" class="card">
                @csrf
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Nouveau rôle</h2>
                        <p class="card-subtitle">Choisissez ce que ce rôle peut faire. Le tableau de bord et « Mon compte » sont toujours accessibles.</p>
                    </div>
                </div>
                <div class="card-body stack" style="gap: 1rem;">
                    <div class="field">
                        <label class="label" for="new_role_name">Nom <span class="req">*</span></label>
                        <input type="text" id="new_role_name" name="name" value="{{ $errors->hasBag('createRole') ? old('name') : '' }}" maxlength="60" required placeholder="Ex. Rédaction" class="input @error('name', 'createRole') is-invalid @enderror">
                        @error('name', 'createRole')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="new_role_description">Description</label>
                        <input type="text" id="new_role_description" name="description" value="{{ $errors->hasBag('createRole') ? old('description') : '' }}" maxlength="255" class="input">
                    </div>
                    @include('admin.settings._permission-checks', ['prefix' => 'new_role', 'checked' => $errors->hasBag('createRole') ? old('permissions', []) : []])
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Créer le rôle</button>
                </div>
            </form>
        </aside>
    </div>
@endsection
