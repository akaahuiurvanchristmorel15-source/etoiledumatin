@extends('layouts.admin')
@section('title', 'Modifier « '.$category->name.' »')

@section('content')
    <div class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('admin.categories.index') }}">Catégories</a><span>/</span><span>Modifier</span>
            </nav>
            <h1 class="page-title">{{ $category->name }}</h1>
        </div>
    </div>

    <form action="{{ route('admin.categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.categories._form')
    </form>
@endsection
