@extends('layouts.app')
@section('canonical', url()->current())

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    <div class="pg pg-legal">
        <header class="pg-head">
            <p class="pg-eyebrow">Informations légales</p>
            <h1 class="pg-title">@yield('legal_title')</h1>
            <p class="pg-subtitle">Dernière mise à jour : 29 octobre 2026</p>
        </header>

        <nav class="pg-legal-nav" aria-label="Pages légales">
            <a href="{{ route('legal.notice') }}" @class(['is-active' => request()->routeIs('legal.notice')])>Mentions légales</a>
            <a href="{{ route('legal.privacy') }}" @class(['is-active' => request()->routeIs('legal.privacy')])>Politique de confidentialité</a>
            <a href="{{ route('legal.terms') }}" @class(['is-active' => request()->routeIs('legal.terms')])>Conditions générales de vente</a>
        </nav>

        <article class="pg-legal-body">
            @yield('legal_body')
        </article>
    </div>
@endsection
