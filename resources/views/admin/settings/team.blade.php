@extends('layouts.admin')
@section('title', 'Paramètres · Équipe')

@section('content')
    @include('admin.settings._tabs')

    <div class="grid-aside">
        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Membres de l'équipe</h2>
                    <p class="card-subtitle">Ce que chaque rôle permet se règle dans <a class="link" href="{{ route('admin.settings.roles') }}">Rôles et permissions</a>.</p>
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
                            @php $isLocked = $member->is(auth()->user()) || $member->isSuperAdmin(); @endphp
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
                                            <select name="role" class="select" style="min-width: 190px;" aria-label="Rôle de {{ $member->name }}" data-autosubmit>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->slug }}" @selected($member->role === $role->slug)>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <noscript><button type="submit" class="btn btn-secondary btn-sm">OK</button></noscript>
                                        </form>
                                    @endif
                                </td>
                                <td class="td-actions">
                                    @unless($isLocked)
                                        <div class="cell-actions">
                                            <form action="{{ route('admin.settings.team.destroy', $member) }}" method="POST" class="inline-form" data-confirm="Retirer l'accès administration de {{ $member->name }} ? Son compte lecteur est conservé.">
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

        <aside>
            <form action="{{ route('admin.settings.team.store') }}" method="POST" class="card" data-member-form>
                @csrf
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Ajouter un membre</h2>
                        <p class="card-subtitle">Si l'adresse correspond déjà à un compte lecteur, ce compte rejoint l'équipe et garde son mot de passe.</p>
                    </div>
                </div>
                <div class="card-body stack" style="gap: 1rem;">
                    <div class="field">
                        <label class="label" for="member_email">E-mail <span class="req">*</span></label>
                        <input type="email" id="member_email" name="email" value="{{ $errors->hasBag('createMember') ? old('email') : '' }}" required maxlength="150" autocomplete="off" class="input @error('email', 'createMember') is-invalid @enderror">
                        @error('email', 'createMember')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="member_name">Nom</label>
                        <input type="text" id="member_name" name="name" value="{{ $errors->hasBag('createMember') ? old('name') : '' }}" maxlength="120" autocomplete="off" class="input @error('name', 'createMember') is-invalid @enderror">
                        <p class="help">Inutile pour un compte lecteur existant.</p>
                        @error('name', 'createMember')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="member_role">Rôle <span class="req">*</span></label>
                        <select id="member_role" name="role" class="select @error('role', 'createMember') is-invalid @enderror">
                            @foreach($roles as $role)
                                <option value="{{ $role->slug }}" @selected(old('role', 'manager') === $role->slug)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role', 'createMember')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="member_password">Mot de passe provisoire</label>
                        <div style="display: flex; gap: .4rem;">
                            <input type="text" id="member_password" name="password" data-password autocomplete="new-password" spellcheck="false" class="input mono @error('password', 'createMember') is-invalid @enderror" style="flex: 1; min-width: 0;">
                            <button type="button" class="btn btn-secondary btn-sm" data-password-generate title="Générer un nouveau mot de passe">Générer</button>
                            <button type="button" class="btn btn-ghost btn-sm" data-password-copy title="Copier le mot de passe">Copier</button>
                        </div>
                        <p class="help">{{ app()->isProduction() ? '10 caractères minimum, avec majuscules, minuscules et chiffres.' : '8 caractères minimum, avec lettres et chiffres.' }} À communiquer au membre : il pourra le changer dans « Mon compte ».</p>
                        @error('password', 'createMember')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <label class="check">
                        <input type="checkbox" name="send_invite" value="1" @checked($errors->hasBag('createMember') ? old('send_invite') : true)>
                        <span><span class="check-title">Envoyer un e-mail d'invitation</span><span class="check-help">Avec un lien pour choisir son propre mot de passe.</span></span>
                    </label>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Ajouter à l'équipe</button>
                </div>
            </form>
        </aside>
    </div>
@endsection

@push('scripts')
    <script nonce="{{ Vite::cspNonce() }}">
        /**
         * Mot de passe provisoire : proposé d'office, régénérable et copiable.
         * Écoute déléguée : le formulaire peut être remplacé par une mise à jour sans rechargement (js/live.js).
         */
        (function () {
            var sets = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789'];

            function pick(chars) {
                return chars[crypto.getRandomValues(new Uint32Array(1))[0] % chars.length];
            }

            function generate(field) {
                var chars = sets.map(pick);

                while (chars.length < 14) {
                    chars.push(pick(sets.join('')));
                }

                for (var i = chars.length - 1; i > 0; i--) {
                    var j = crypto.getRandomValues(new Uint32Array(1))[0] % (i + 1);
                    var swap = chars[i]; chars[i] = chars[j]; chars[j] = swap;
                }

                field.value = chars.join('');
            }

            function prefill() {
                document.querySelectorAll('[data-member-form] [data-password]').forEach(function (field) {
                    if (!field.value) { generate(field); }
                });
            }

            document.addEventListener('click', function (event) {
                var form = event.target.closest('[data-member-form]');
                var field = form && form.querySelector('[data-password]');

                if (!field) { return; }

                if (event.target.closest('[data-password-generate]')) {
                    generate(field);
                } else if (event.target.closest('[data-password-copy]') && navigator.clipboard) {
                    var button = event.target.closest('[data-password-copy]');
                    navigator.clipboard.writeText(field.value).then(function () {
                        button.textContent = 'Copié';
                        setTimeout(function () { button.textContent = 'Copier'; }, 1500);
                    });
                }
            });

            prefill();
            document.addEventListener('live:updated', prefill);
        })();
    </script>
@endpush
