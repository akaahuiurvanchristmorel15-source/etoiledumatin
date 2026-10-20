@extends('layouts.admin')
@section('title', 'Paramètres · Livraison')

@section('content')
    @include('admin.settings._tabs')

    <form action="{{ route('admin.settings.shipping.update') }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-header">
            <div>
                <h2 class="card-title">Frais de livraison</h2>
                <p class="card-subtitle">Appliqués aux commandes contenant au moins un livre papier.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Zone</th>
                        <th style="width: 180px;">Tarif (FCFA)</th>
                        <th style="width: 220px;">Délai affiché</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($zones as $key => $zone)
                        <tr>
                            <td class="cell-primary">
                                <div class="cell-title">{{ $zone['name'] }}</div>
                                <div class="cell-sub">{{ $zone['description'] }}</div>
                            </td>
                            <td data-label="Tarif">
                                <input type="number" name="zones[{{ $key }}][price]" value="{{ old("zones.$key.price", $zone['price']) }}" min="0" required class="input @error("zones.$key.price") is-invalid @enderror" aria-label="Tarif {{ $zone['name'] }}">
                                @error("zones.$key.price")<p class="error">{{ $message }}</p>@enderror
                            </td>
                            <td data-label="Délai">
                                <input type="text" name="zones[{{ $key }}][delay]" value="{{ old("zones.$key.delay", $zone['delay']) }}" maxlength="60" required class="input @error("zones.$key.delay") is-invalid @enderror" aria-label="Délai {{ $zone['name'] }}">
                                @error("zones.$key.delay")<p class="error">{{ $message }}</p>@enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Enregistrer les tarifs</button>
        </div>
    </form>
@endsection
