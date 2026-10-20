@switch($status)
    @case('paid')
        <span class="badge badge-success">Payée</span>
        @break
    @case('pending')
        <span class="badge badge-warning">En attente</span>
        @break
    @case('refunded')
        <span class="badge badge-info">Remboursée</span>
        @break
    @default
        <span class="badge badge-danger">Échouée</span>
@endswitch
