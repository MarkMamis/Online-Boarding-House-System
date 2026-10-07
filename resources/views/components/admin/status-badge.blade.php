@props([
    'status' => 'pending',
    'label' => null,
    'icon' => null,
])

@php
    $normalized = strtolower(trim((string)$status));
    
    // Semantic group mapping
    $semanticClass = match($normalized) {
        'approved', 'active', 'valid', 'completed', 'verified' => 'status-success',
        'pending', 'expiring_soon', 'expiring soon', 'needs review', 'under_review', 'in_progress' => 'status-warning',
        'rejected', 'expired', 'non-compliant', 'inactive', 'cancelled', 'terminated' => 'status-danger',
        'missing', 'not_submitted', 'draft', 'unknown', '' => 'status-neutral',
        'info', 'processing' => 'status-info',
        default => 'status-neutral'
    };

    $displayLabel = $label ?? match($normalized) {
        'expiring_soon' => 'Expiring Soon',
        'not_submitted' => 'Not Submitted',
        'under_review' => 'Under Review',
        'in_progress' => 'In Progress',
        default => ucwords(str_replace('_', ' ', $normalized ?: 'Unknown'))
    };

    $defaultIcon = match($normalized) {
        'approved', 'active', 'valid', 'completed', 'verified' => 'bi-check-circle-fill',
        'pending', 'under_review', 'in_progress' => 'bi-hourglass-split',
        'expiring_soon', 'expiring soon' => 'bi-clock-history',
        'rejected', 'expired', 'cancelled' => 'bi-x-circle-fill',
        'missing', 'not_submitted' => 'bi-dash-circle',
        default => null
    };

    $iconClass = $icon ?? $defaultIcon;
@endphp

<span class="admin-status-badge {{ $semanticClass }} d-inline-flex align-items-center gap-1">
    @if($iconClass)
        <i class="bi {{ $iconClass }} status-icon"></i>
    @endif
    <span>{{ $displayLabel }}</span>
</span>
