@extends('layouts.admin')
@section('title', 'Paramètres · Équipe')

@section('content')
    @include('admin.settings._tabs')

    <div class="grid-aside">
        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Membres de l'équipe</h2>
                    <p class="card-subtitle">Gestionnaire : livres, catégories et avis · Logistique : commandes et expéditions · Administrateur : tout.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th>Membre</th>
                            <th>Rôle</th>
                            <th class="text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                            @php $isLocked = $member->is(auth()->user()) || $member->role === 'super_admin'; @endphp
                            <tr>
                                <td class="cell-primary">
                                    <div class="cell-title">{{ $member->name }} @if($member->is(auth()->user()))<span class="badge" style="margin-left: .3rem;">Vous</span>@endif</div>
                                    <div class="cell-sub">{{ $member->email }}</div>
                                </td>
                                <td data-label="Rôle">
                                    @if($isLocked)
                                        <span class="badge badge-accent">{{ $member->roleLabel() }}</span>
                                    @else
                                        <form action="{{ route('admin.settings.team.update', $member) }}" method="POST" class="role-form">
                                            @csrf
                                            @method('PUT')
                                            <select name="role" class="select" style="min-width: 190px;" aria-label="Rôle de {{ $member->name }}">
                                                @foreach($roles as $value => $label)
                                                    <option value="{{ $value }}" @selected($member->role === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-secondary btn-sm">OK</button>
                                        </form>
                                    @endif
                                </td>
                                <td class="td-actions">
                                    @unless($isLocked)
                                        <div class="cell-actions">
                                            <form action="{{ route('admin.settings.team.destroy', $member) }}" method="POST" class="inline-form" data-confirm="Retirer l'accès administration de {{ $member->name }} ?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Retirer l'accès</button>
                                            </form>
                                        </div>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <form action="{{ route('admin.settings.team.store') }}" method="POST" class="card">
            @csrf
            <div class="card-header"><h2 class="card-title">Ajouter un membre</h2></div>
            <div class="card-body stack" style="gap: 1rem;">
                <div class="field">
                    <label class="label" for="member_name">Nom <span class="req">*</span></label>
                    <input type="text" id="member_name" name="name" value="{{ old('name') }}" required maxlength="120" class="input @error('name', 'createMember') is-invalid @enderror">
                    @error('name', 'createMember')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="member_email">E-mail <span class="req">*</span></label>
                    <input type="email" id="member_email" name="email" value="{{ old('email') }}" required maxlength="150" class="input @error('email', 'createMember') is-invalid @enderror">
                    @error('email', 'createMember')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="member_role">Rôle <span class="req">*</span></label>
                    <select id="member_role" name="role" class="select @error('role', 'createMember') is-invalid @enderror">
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', 'manager') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role', 'createMember')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="member_password">Mot de passe provisoire <span class="req">*</span></label>
                    <input type="text" id="member_password" name="password" required minlength="8" class="input @error('password', 'createMember') is-invalid @enderror" autocomplete="new-password">
                    <p class="help">À communiquer au membre, qui pourra le changer dans ses paramètres.</p>
                    @error('password', 'createMember')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
@endsection
