@extends('layouts.admin')
@section('title', 'Sécurité')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Journal de sécurité</h1>
            <p class="page-subtitle">Activités suspectes enregistrées automatiquement. Les super administrateurs reçoivent un e-mail quand une alerte apparaît (vérification toutes les 15 minutes).</p>
        </div>
    </div>

    <section class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <div>
                <h2 class="card-title">Alertes des dernières 24 heures</h2>
            </div>
        </div>
        <div class="card-body">
            @forelse($findings as $finding)
                <div class="alert alert-error" role="alert" style="margin-bottom: .6rem;">
                    <x-admin.icon name="alert" />
                    <span>{{ $finding }}</span>
                </div>
            @empty
                <p class="cell-sub" style="margin: 0;">Rien d'anormal depuis 24 heures.</p>
            @endforelse
        </div>
    </section>

    <div class="grid-aside" style="margin-bottom: 1.5rem;">
        <section class="card">
            <div class="card-header"><h2 class="card-title">Événements en 24 heures</h2></div>
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                        @forelse($countsByAction as $action => $total)
                            <tr>
                                <td><a class="link" href="{{ route('admin.security.index', ['type' => $action]) }}">{{ \App\Models\AuditLog::SUSPICIOUS_ACTIONS[$action] ?? $action }}</a></td>
                                <td class="text-right">{{ $total }}</td>
                            </tr>
                        @empty
                            <tr><td class="cell-sub">Aucun événement.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-header"><h2 class="card-title">Adresses IP les plus actives</h2></div>
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                        @forelse($topAddresses as $address => $total)
                            <tr>
                                <td><code>{{ $address }}</code></td>
                                <td class="text-right">{{ $total }}</td>
                            </tr>
                        @empty
                            <tr><td class="cell-sub">Aucune adresse.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="card">
        <form action="{{ route('admin.security.index') }}" method="GET" class="toolbar">
            <label for="type" class="sr-only">Type d'événement</label>
            <select id="type" name="type" class="select" data-autosubmit>
                <option value="">Tous les événements</option>
                @foreach(\App\Models\AuditLog::SUSPICIOUS_ACTIONS as $action => $label)
                    <option value="{{ $action }}" @selected($selectedType === $action)>{{ $label }}</option>
                @endforeach
            </select>
            <noscript><button type="submit" class="btn btn-secondary">Filtrer</button></noscript>
        </form>

        <div class="table-wrap">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Événement</th>
                        <th>Compte</th>
                        <th>Adresse IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td data-label="Date">{{ $event->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td data-label="Événement" class="cell-primary">
                                <div class="cell-title">{{ $event->label() }}</div>
                                @if($event->details)
                                    <div class="cell-sub">{{ collect($event->details)->map(fn ($value, $key) => $key.' : '.(is_scalar($value) ? $value : json_encode($value)))->implode(' · ') }}</div>
                                @endif
                            </td>
                            <td data-label="Compte">{{ $event->user?->email ?? '—' }}</td>
                            <td data-label="Adresse IP"><code>{{ $event->ip_address ?? '—' }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty">
                                    <p class="empty-title">Aucune activité suspecte enregistrée</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $events->links('admin.partials.pagination') }}
    </div>
@endsection
