@extends('layouts.admin')
@section('title', 'Paramètres · Contenus du site')

@section('content')
    @include('admin.settings._tabs')

    <form action="{{ route('admin.settings.content.update') }}" method="POST" class="stack">
        @csrf
        @method('PUT')

        @foreach($fields as $group => $groupFields)
            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">{{ $group }}</h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        @foreach($groupFields as $name => $field)
                            <div class="field">
                                <label class="label" for="content_{{ $name }}">{{ $field['label'] }}</label>
                                <input type="text" id="content_{{ $name }}" name="{{ $name }}" value="{{ old($name, \App\Models\Setting::content($name)) }}" class="input @error($name) is-invalid @enderror">
                                @isset($field['help'])<p class="help">{{ $field['help'] }}</p>@endisset
                                @error($name)<p class="error">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach

        <div>
            <button type="submit" class="btn btn-primary">Enregistrer les contenus</button>
        </div>
    </form>
@endsection
