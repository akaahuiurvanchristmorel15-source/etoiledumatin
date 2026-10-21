@extends('layouts.app')
@section('title', 'Mot de passe oublié — NSOROMMA')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Recevez un lien pour réinitialiser le mot de passe de votre compte NSOROMMA.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    <div class="pg">
        <div class="pg-auth-simple">
            <p class="pg-eyebrow">Espace lecteur</p>
            <h1 class="pg-title">Mot de passe oublié</h1>
            <p class="pg-subtitle">Saisissez l'adresse e-mail de votre compte : nous vous enverrons un lien valable 60 minutes pour choisir un nouveau mot de passe.</p>

            <form action="{{ route('password.email') }}" method="POST" class="pg-form" style="margin-top: 2rem;">
                @csrf
                <div class="pg-field">
                    <label class="pg-label" for="email">Adresse e-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus maxlength="150" autocomplete="email" class="pg-input @error('email') is-invalid @enderror">
                    @error('email')<span class="pg-error">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="pg-btn pg-btn-block">Envoyer le lien</button>
            </form>

            <div class="pg-auth-switch">
                Vous vous en souvenez ? <a href="{{ route('login') }}">Se connecter</a>
            </div>
        </div>
    </div>
@endsection
