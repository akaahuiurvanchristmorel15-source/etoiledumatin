@extends('layouts.admin')
@section('title', 'Paramètres · Mon compte')

@section('content')
    @include('admin.settings._tabs')

    <div class="grid-2">
        <form action="{{ route('admin.settings.profile.update') }}" method="POST" class="card">
            @csrf
            @method('PUT')
            <div class="card-header">
                <div>
                    <h2 class="card-title">Informations personnelles</h2>
                    <p class="card-subtitle">Rôle : {{ $user->roleLabel() }}</p>
                </div>
            </div>
            <div class="card-body stack" style="gap: 1rem;">
                <div class="field">
                    <label class="label" for="name">Nom complet <span class="req">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="120" class="input @error('name') is-invalid @enderror" autocomplete="name">
                    @error('name')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="email">E-mail <span class="req">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="150" class="input @error('email') is-invalid @enderror" autocomplete="email">
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="25" class="input @error('phone') is-invalid @enderror" autocomplete="tel">
                    @error('phone')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>

        <form action="{{ route('admin.settings.password.update') }}" method="POST" class="card">
            @csrf
            @method('PUT')
            <div class="card-header">
                <div>
                    <h2 class="card-title">Mot de passe</h2>
                    <p class="card-subtitle">8 caractères minimum.</p>
                </div>
            </div>
            <div class="card-body stack" style="gap: 1rem;">
                <div class="field">
                    <label class="label" for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" required class="input @error('current_password', 'password') is-invalid @enderror" autocomplete="current-password">
                    @error('current_password', 'password')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required class="input @error('password', 'password') is-invalid @enderror" autocomplete="new-password">
                    @error('password', 'password')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="label" for="password_confirmation">Confirmation</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required class="input" autocomplete="new-password">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
            </div>
        </form>
    </div>
@endsection
