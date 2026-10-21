@extends('layouts.admin')
@section('title', 'Modifier « '.$book->title.' »')

@section('content')
    <div class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('admin.books.index') }}">Livres</a><span>/</span><span>Modifier</span>
            </nav>
            <h1 class="page-title">{{ $book->title }}</h1>
            <p class="page-subtitle">{{ $book->author }}</p>
        </div>
        <div class="page-actions">
            @if($book->is_active)
                <a href="{{ route('catalog.show', $book->slug) }}" class="btn btn-secondary">
                    <x-admin.icon name="external" />
                    <span>Voir la fiche</span>
                </a>
            @endif
        </div>
    </div>

    <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.books._form')
    </form>
@endsection
