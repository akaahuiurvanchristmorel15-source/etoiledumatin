@extends('layouts.app')
@section('title', 'Nouveau mot de passe — NSOROMMA')
@section('meta_description', 'Choisissez un nouveau mot de passe pour votre compte NSOROMMA.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    <div class="pg">
        <div class="pg-auth-simple">
            <p class="pg-eyebrow">Espace lecteur</p>
            <h1 class="pg-title">Nouveau mot de passe</h1>
            <p class="pg-subtitle">Choisissez un mot de passe que vous n'utilisez pas ailleurs : {{ app()->isProduction() ? '10 caractères minimum, avec majuscules, minuscules et chiffres' : '8 caractères minimum, avec lettres et chiffres' }}.</p>

            <form action="{{ route('password.update') }}" method="POST" class="pg-form" style="margin-top: 2rem;" x-data="{ showPassword: false }">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="pg-field">
                    <label class="pg-label" for="email">Adresse e-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required maxlength="150" autocomplete="username" class="pg-input @error('email') is-invalid @enderror">
                    @error('email')<span class="pg-error">{{ $message }}</span>@enderror
                </div>

                <div class="pg-field">
                    <label class="pg-label" for="password">Nouveau mot de passe</label>
                    <div class="pg-password">
                        <input id="password" :type="showPassword ? 'text' : 'password'" type="password" name="password" required minlength="8" maxlength="255" autofocus autocomplete="new-password" class="pg-input @error('password') is-invalid @enderror">
                        <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" aria-label="Afficher le mot de passe">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('password')<span class="pg-error">{{ $message }}</span>@enderror
                </div>

                <div class="pg-field">
                    <label class="pg-label" for="password_confirmation">Confirmation</label>
                    <input id="password_confirmation" :type="showPassword ? 'text' : 'password'" type="password" name="password_confirmation" required autocomplete="new-password" class="pg-input">
                </div>

                <button type="submit" class="pg-btn pg-btn-block">Enregistrer le nouveau mot de passe</button>
            </form>
        </div>
    </div>
@endsection
