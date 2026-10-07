@props([
    'icon' => 'bi-inbox',
    'title' => 'No records found',
    'message' => null,
    'description' => null,
    'actionText' => null,
    'actionLabel' => null,
    'actionUrl' => null,
    'actionLink' => null,
    'actionIcon' => null,
])

@php
    $displayMessage = $description ?? ($message ?? 'There are no items matching your criteria at this time.');
    $finalActionUrl = $actionUrl ?? $actionLink;
    $finalActionText = $actionText ?? $actionLabel;
@endphp

<div class="admin-empty-state text-center py-5 px-3">
    <div class="admin-empty-icon mb-3">
        <i class="bi {{ $icon }}"></i>
    </div>
    <h3 class="admin-empty-title h5 mb-1 fw-bold text-dark">{{ $title }}</h3>
    <p class="admin-empty-message text-muted small mx-auto mb-0" style="max-width: 460px;">{{ $displayMessage }}</p>
    @if($finalActionText && $finalActionUrl)
        <div class="mt-3">
            <a href="{{ $finalActionUrl }}" class="btn btn-sm btn-outline-success px-3">
                @if($actionIcon)<i class="bi {{ $actionIcon }} me-1"></i>@endif
                {{ $finalActionText }}
            </a>
        </div>
    @endif
</div>
