@extends('layouts.app')
@section('title', 'Confirmez votre e-mail — NSOROMMA')
@section('meta_description', 'Confirmez votre adresse e-mail pour sécuriser votre compte NSOROMMA.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    <div class="pg">
        <div class="pg-auth-simple">
            <span class="pg-success-mark is-pending" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="3 7 12 13 21 7"/></svg>
            </span>
            <p class="pg-eyebrow">Dernière étape</p>
            <h1 class="pg-title">Confirmez votre e-mail</h1>
            <p class="pg-subtitle">Nous avons envoyé un lien de confirmation à <strong>{{ auth()->user()->email }}</strong>. Cliquez dessus pour sécuriser votre compte. Pensez à vérifier vos courriers indésirables.</p>

            <div class="pg-actions">
                <form action="{{ route('verification.send') }}" method="POST">
                    @csrf
                    <button type="submit" class="pg-btn">Renvoyer le lien</button>
                </form>
                <a href="{{ route('catalog.index') }}" class="pg-btn pg-btn-ghost">Parcourir le catalogue</a>
            </div>

            <p class="pg-note" style="text-align: left; margin-top: 2rem;">Vous pouvez déjà acheter et télécharger vos livres. La confirmation protège votre compte et permet de récupérer votre mot de passe en cas d'oubli.</p>
        </div>
    </div>
@endsection
