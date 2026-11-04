@extends('layouts.app')
@section('title', 'Lien de téléchargement expiré — NSOROMMA')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Ce lien de téléchargement a expiré : générez-en un nouveau depuis votre commande.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    <div class="pg">
        <div class="pg-empty">
            <p class="pg-empty-title">Ce lien de téléchargement a expiré</p>
            @auth
                <p>Par sécurité, chaque lien n'est valable que 15 minutes.<br>Votre livre reste disponible : cliquez de nouveau sur «&nbsp;Télécharger&nbsp;» dans votre bibliothèque.</p>
                <a href="{{ route('library.index') }}" class="pg-btn">Ma bibliothèque</a>
            @else
                <p>Par sécurité, chaque lien n'est valable que 15 minutes.<br>Votre livre reste disponible : retrouvez votre commande avec son numéro, puis cliquez de nouveau sur «&nbsp;Télécharger&nbsp;».</p>
                <a href="{{ route('orders.lookup') }}" class="pg-btn">Retrouver ma commande</a>
            @endauth
        </div>
    </div>
@endsection
