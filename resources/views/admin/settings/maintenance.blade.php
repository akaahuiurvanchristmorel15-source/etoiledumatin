@extends('layouts.admin')
@section('title', 'Paramètres · Maintenance')

@section('content')
    @include('admin.settings._tabs')

    <form action="{{ route('admin.settings.maintenance.update') }}" method="POST" class="card"
          @if(! $isActive) data-confirm="Activer le mode maintenance ? Les visiteurs ne pourront plus accéder au site jusqu'à sa désactivation." @endif>
        @csrf
        @method('PUT')
        <input type="hidden" name="enabled" value="{{ $isActive ? '0' : '1' }}">

        <div class="card-header">
            <div>
                <h2 class="card-title">Mode maintenance</h2>
                <p class="card-subtitle">Pendant des modifications techniques, les visiteurs voient « Le site sera bientôt disponible ». L'administration et sa page de connexion restent accessibles, et l'équipe connectée continue de voir le site normalement.</p>
            </div>
            @if($isActive)
                <span class="badge badge-warning">Activé</span>
            @else
                <span class="badge badge-success">Site ouvert</span>
            @endif
        </div>

        <div class="card-body stack" style="gap: 1rem;">
            <div class="field">
                <label class="label" for="maintenance_message">Message affiché aux visiteurs</label>
                <textarea id="maintenance_message" name="message" rows="3" maxlength="500" class="textarea @error('message') is-invalid @enderror" placeholder="Nous effectuons quelques améliorations techniques. Merci de votre patience, revenez dans un instant.">{{ old('message', $maintenanceMessage) }}</textarea>
                <p class="help">Facultatif : laissez vide pour le message par défaut. Les paiements Paystack en cours restent confirmés automatiquement.</p>
                @error('message')<p class="error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="card-footer">
            @if($isActive)
                <button type="submit" class="btn btn-primary">Désactiver la maintenance et rouvrir le site</button>
            @else
                <button type="submit" class="btn btn-danger">Activer la maintenance</button>
            @endif
        </div>
    </form>
@endsection
