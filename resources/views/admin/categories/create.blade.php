@extends('layouts.admin')
@section('title', 'Nouvelle catégorie')

@section('content')
    <div class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('admin.categories.index') }}">Catégories</a><span>/</span><span>Nouvelle</span>
            </nav>
            <h1 class="page-title">Nouvelle catégorie</h1>
        </div>
    </div>

    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        @include('admin.categories._form')
    </form>
@endsection
