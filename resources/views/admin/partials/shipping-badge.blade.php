@if($shipping)
    <span class="badge {{ match ($shipping->status) { 'delivered' => 'badge-success', 'shipped' => 'badge-info', 'cancelled' => 'badge-danger', default => 'badge-warning' } }}">{{ $shipping->status_label }}</span>
@else
    <span class="text-muted">Numérique</span>
@endif
