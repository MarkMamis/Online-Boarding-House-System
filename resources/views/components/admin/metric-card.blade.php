@props([
    'label',
    'value',
    'sub' => null,
    'subtext' => null,
    'variant' => 'neutral', // neutral, brand, primary, success, warning, danger, info
    'icon' => null,
    'href' => null,
    'url' => null,
])

@php
    $finalSub = $subtext ?? $sub;
    $finalHref = $url ?? $href;
    $normVariant = strtolower(trim((string)$variant));

    $valClass = match($normVariant) {
        'brand', 'primary', 'success' => 'text-success',
        'warning' => 'text-warning-emphasis',
        'danger' => 'text-danger',
        'info' => 'text-info-emphasis',
        default => 'text-dark'
    };

    $iconBg = match($normVariant) {
        'brand', 'primary', 'success' => 'bg-success-subtle text-success border border-success-subtle',
        'warning' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        'danger' => 'bg-danger-subtle text-danger border border-danger-subtle',
        'info' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
        default => 'bg-light text-secondary border border-secondary-subtle'
    };
@endphp

<div class="admin-metric-card h-100 {{ $finalHref ? 'is-link' : '' }}">
    @if($finalHref)
        <a href="{{ $finalHref }}" class="text-decoration-none text-reset d-block h-100">
    @endif

    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
        <div class="admin-metric-label">{{ $label }}</div>
        @if($icon)
            <div class="admin-metric-icon {{ $iconBg }}">
                <i class="bi {{ $icon }}"></i>
            </div>
        @endif
    </div>

    <div class="admin-metric-value {{ $valClass }}">{{ $value }}</div>

    @if($finalSub)
        <div class="admin-metric-sub">{{ $finalSub }}</div>
    @endif

    @if($finalHref)
        </a>
    @endif
</div>
