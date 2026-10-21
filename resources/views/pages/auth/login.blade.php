@extends('layouts.app')
@section('title', 'Connexion — NSOROMMA')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Connectez-vous à votre espace lecteur pour accéder à votre bibliothèque et à vos commandes.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    <div class="pg">
        <div class="pg-auth">
            @include('pages.auth._brand')

            <div class="pg-auth-form">
                <p class="pg-eyebrow">Espace lecteur</p>
                <h1 class="pg-title">Connexion</h1>
                <p class="pg-subtitle">Retrouvez votre bibliothèque, vos téléchargements et le suivi de vos commandes.</p>

                <div style="margin-top: 2.25rem;">
                    @include('pages.auth._google')
                </div>

                <form action="{{ route('login') }}" method="POST" class="pg-form" style="margin-top: 1.5rem;" x-data="{ showPassword: false }">
                    @csrf

                    <div class="pg-field">
                        <label class="pg-label" for="email">Adresse e-mail</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus maxlength="150" autocomplete="username" class="pg-input @error('email') is-invalid @enderror">
                        @error('email')<span class="pg-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="pg-field">
                        <label class="pg-label" for="password">Mot de passe</label>
                        <div class="pg-password">
                            <input id="password" :type="showPassword ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" class="pg-input @error('password') is-invalid @enderror">
                            <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" aria-label="Afficher le mot de passe">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        @error('password')<span class="pg-error">{{ $message }}</span>@enderror
                    </div>

                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: .75rem;">
                        <label class="pg-checkbox">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                            <span>Rester connecté</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="pg-link">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="pg-btn pg-btn-block">
                        <span>Se connecter</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </form>

                <div class="pg-auth-switch">
                    Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte</a><br>
                    Commande passée sans compte ? <a href="{{ route('orders.lookup') }}">Suivre ma commande</a>
                </div>
            </div>
        </div>
    </div>
@endsection
