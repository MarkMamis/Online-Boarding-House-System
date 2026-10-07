@props([
    'label',
    'value',
    'sub' => null,
    'variant' => 'neutral', // neutral, primary, success, warning, danger, info
    'icon' => null,
    'href' => null,
])

@php
    $valClass = match($variant) {
        'primary', 'success' => 'text-success',
        'warning' => 'text-warning',
        'danger' => 'text-danger',
        'info' => 'text-info',
        default => 'text-dark'
    };

    $iconBg = match($variant) {
        'primary', 'success' => 'bg-success-subtle text-success border-success-subtle',
        'warning' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        'danger' => 'bg-danger-subtle text-danger border-danger-subtle',
        'info' => 'bg-info-subtle text-info-emphasis border-info-subtle',
        default => 'bg-light text-secondary border-secondary-subtle'
    };
@endphp

<div class="admin-metric-card h-100 {{ $href ? 'is-link' : '' }}">
    @if($href)
        <a href="{{ $href }}" class="text-decoration-none text-reset d-block h-100">
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

    @if($sub)
        <div class="admin-metric-sub">{{ $sub }}</div>
    @endif

    @if($href)
        </a>
    @endif
</div>
