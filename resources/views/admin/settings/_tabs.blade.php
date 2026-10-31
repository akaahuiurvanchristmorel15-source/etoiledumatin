<div class="page-header">
    <div>
        <h1 class="page-title">Paramètres</h1>
        <p class="page-subtitle">Votre compte et, selon vos permissions, la boutique, l'équipe et les rôles.</p>
    </div>
</div>

<nav class="tabs" aria-label="Sections des paramètres">
    <a href="{{ route('admin.settings.profile') }}" class="{{ request()->routeIs('admin.settings.profile') ? 'is-active' : '' }}">Mon compte</a>
    @can('manage-store')
        <a href="{{ route('admin.settings.content') }}" class="{{ request()->routeIs('admin.settings.content') ? 'is-active' : '' }}">Contenus du site</a>
        <a href="{{ route('admin.settings.shipping') }}" class="{{ request()->routeIs('admin.settings.shipping') ? 'is-active' : '' }}">Livraison</a>
        <a href="{{ route('admin.settings.maintenance') }}" class="{{ request()->routeIs('admin.settings.maintenance') ? 'is-active' : '' }}">Maintenance</a>
    @endcan
    @can('manage-team')
        <a href="{{ route('admin.settings.team') }}" class="{{ request()->routeIs('admin.settings.team') ? 'is-active' : '' }}">Équipe</a>
        <a href="{{ route('admin.settings.roles') }}" class="{{ request()->routeIs('admin.settings.roles') ? 'is-active' : '' }}">Rôles et permissions</a>
    @endcan
</nav>
