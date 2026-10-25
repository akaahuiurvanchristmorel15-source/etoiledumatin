@extends('layouts.admin')
@section('title', 'Modifier « '.$article->title.' »')

@section('content')
    <div class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('admin.news.index') }}">Actualités</a><span>/</span><span>Modifier</span>
            </nav>
            <h1 class="page-title">{{ $article->title }}</h1>
        </div>
    </div>

    <form action="{{ route('admin.news.update', $article) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.news._form')
    </form>
@endsection
