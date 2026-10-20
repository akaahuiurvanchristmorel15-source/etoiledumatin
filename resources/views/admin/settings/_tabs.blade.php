<div class="page-header">
    <div>
        <h1 class="page-title">Paramètres</h1>
        <p class="page-subtitle">Votre compte{{ Gate::allows('manage-store') ? ', la livraison et l\'équipe' : '' }}.</p>
    </div>
</div>

<nav class="tabs" aria-label="Sections des paramètres">
    <a href="{{ route('admin.settings.profile') }}" class="{{ request()->routeIs('admin.settings.profile') ? 'is-active' : '' }}">Mon compte</a>
    @can('manage-store')
        <a href="{{ route('admin.settings.shipping') }}" class="{{ request()->routeIs('admin.settings.shipping') ? 'is-active' : '' }}">Livraison</a>
        <a href="{{ route('admin.settings.team') }}" class="{{ request()->routeIs('admin.settings.team') ? 'is-active' : '' }}">Équipe</a>
    @endcan
</nav>
