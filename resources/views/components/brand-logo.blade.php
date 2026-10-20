@props(['size' => 32, 'withName' => true, 'nameSize' => '1.1rem'])

<span {{ $attributes->merge(['class' => 'brand-logo']) }} style="display: inline-flex; align-items: center; gap: .55rem; line-height: 1;">
    <img src="{{ asset('images/brand/nsoromma-mark.png') }}" alt="{{ $withName ? '' : 'NSOROMMA' }}" width="{{ $size }}" height="{{ $size }}" style="width: {{ $size }}px; height: {{ $size }}px; object-fit: contain; flex-shrink: 0;">
    @if($withName)
        <span style="font-family: Georgia, 'Times New Roman', serif; font-weight: 500; font-size: {{ $nameSize }}; letter-spacing: .08em; color: #111;">NSOROMMA</span>
    @endif
</span>
