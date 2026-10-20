@extends('layouts.app')
@section('title', 'Créer un compte — NSOROMMA')
@section('meta_description', 'Créez votre compte lecteur pour télécharger vos e-books et suivre vos commandes de livres.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    <div class="pg">
        <div class="pg-auth">
            @include('pages.auth._brand')

            <div class="pg-auth-form">
                <p class="pg-eyebrow">Nouveau lecteur</p>
                <h1 class="pg-title">Créer un compte</h1>
                <p class="pg-subtitle">Quelques secondes suffisent pour rassembler vos livres au même endroit.</p>

                <form action="{{ route('register') }}" method="POST" class="pg-form" style="margin-top: 2.25rem;" x-data="{ showPassword: false }">
                    @csrf

                    <div class="pg-field">
                        <label class="pg-label" for="name">Nom complet</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus maxlength="120" autocomplete="name" class="pg-input @error('name') is-invalid @enderror">
                        @error('name')<span class="pg-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="pg-grid-2">
                        <div class="pg-field">
                            <label class="pg-label" for="email">Adresse e-mail</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="150" autocomplete="email" class="pg-input @error('email') is-invalid @enderror">
                            @error('email')<span class="pg-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="pg-field">
                            <label class="pg-label" for="phone">Téléphone</label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required maxlength="25" autocomplete="tel" placeholder="+225 07 00 00 00 00" class="pg-input @error('phone') is-invalid @enderror">
                            @error('phone')<span class="pg-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="pg-grid-2">
                        <div class="pg-field">
                            <label class="pg-label" for="password">Mot de passe</label>
                            <div class="pg-password">
                                <input id="password" :type="showPassword ? 'text' : 'password'" type="password" name="password" required minlength="8" autocomplete="new-password" class="pg-input @error('password') is-invalid @enderror">
                                <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" aria-label="Afficher le mot de passe">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            <span class="pg-hint">8 caractères minimum.</span>
                            @error('password')<span class="pg-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="pg-field">
                            <label class="pg-label" for="password_confirmation">Confirmation</label>
                            <input id="password_confirmation" :type="showPassword ? 'text' : 'password'" type="password" name="password_confirmation" required autocomplete="new-password" class="pg-input">
                        </div>
                    </div>

                    <button type="submit" class="pg-btn pg-btn-block">
                        <span>Créer mon compte</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </form>

                <div class="pg-auth-switch">
                    Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a>
                </div>
            </div>
        </div>
    </div>
@endsection
