@extends('layouts.admin')
@section('title', 'Nouveau livre')

@section('content')
    <div class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('admin.books.index') }}">Livres</a><span>/</span><span>Nouveau</span>
            </nav>
            <h1 class="page-title">Nouveau livre</h1>
        </div>
    </div>

    <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.books._form')
    </form>
@endsection
