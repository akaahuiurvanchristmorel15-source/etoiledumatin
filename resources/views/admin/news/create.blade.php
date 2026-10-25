@extends('layouts.admin')
@section('title', 'Nouvelle actualité')

@section('content')
    <div class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('admin.news.index') }}">Actualités</a><span>/</span><span>Nouvelle</span>
            </nav>
            <h1 class="page-title">Nouvelle actualité</h1>
        </div>
    </div>

    <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.news._form')
    </form>
@endsection
