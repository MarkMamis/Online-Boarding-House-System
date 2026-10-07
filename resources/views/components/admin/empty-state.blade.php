@props([
    'icon' => 'bi-inbox',
    'title' => 'No records found',
    'message' => 'There are no items matching your criteria at this time.',
    'actionText' => null,
    'actionUrl' => null,
    'actionIcon' => null,
])

<div class="admin-empty-state text-center py-5 px-3">
    <div class="admin-empty-icon mb-3">
        <i class="bi {{ $icon }}"></i>
    </div>
    <h3 class="admin-empty-title h5 mb-1 fw-bold text-dark">{{ $title }}</h3>
    <p class="admin-empty-message text-muted small mx-auto mb-0" style="max-width: 440px;">{{ $message }}</p>
    @if($actionText && $actionUrl)
        <div class="mt-3">
            <a href="{{ $actionUrl }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                @if($actionIcon)<i class="bi {{ $actionIcon }} me-1"></i>@endif
                {{ $actionText }}
            </a>
        </div>
    @endif
</div>
