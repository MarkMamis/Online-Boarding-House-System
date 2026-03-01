@php
    $messages = [
        'success' => ['session' => 'success', 'bg' => '#22c55e', 'icon' => 'bi-check-circle-fill'],
        'error'   => ['session' => 'error',   'bg' => '#ef4444', 'icon' => 'bi-x-circle-fill'],
        'warning' => ['session' => 'warning', 'bg' => '#f59e0b', 'icon' => 'bi-exclamation-triangle-fill'],
        'info'    => ['session' => 'info',    'bg' => '#3b82f6', 'icon' => 'bi-info-circle-fill'],
    ];
    $toasts = collect($messages)->filter(fn($m) => session($m['session']))->values();
@endphp

@if($toasts->isNotEmpty())
{{-- Toast container --}}
<div aria-live="polite" aria-atomic="true">
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090; margin-top: .75rem;">
        @foreach($toasts as $i => $m)
            <div id="toast-{{ $i }}"
                 class="toast align-items-center border-0 shadow-lg"
                 role="alert"
                 aria-live="assertive"
                 aria-atomic="true"
                 data-bs-autohide="true"
                 data-bs-delay="4500"
                 style="
                    background: {{ $m['bg'] }};
                    --bs-toast-border-radius: 1rem;
                    min-width: 300px;
                    backdrop-filter: blur(8px);
                 ">
                <div class="d-flex align-items-center px-3 py-3">
                    <i class="bi {{ $m['icon'] }} fs-5 me-2 flex-shrink-0" style="color: #fff; opacity: .95;"></i>
                    <div class="toast-body fw-semibold p-0 me-auto" style="color: #fff; font-size: .92rem; line-height: 1.4;">
                        {{ session($m['session']) }}
                    </div>
                    <button type="button"
                            class="ms-3 flex-shrink-0 btn-close btn-close-white"
                            data-bs-dismiss="toast"
                            aria-label="Close"
                            style="opacity: .7;"></button>
                </div>
                {{-- Progress bar --}}
                <div class="toast-progress" style="
                    height: 3px;
                    background: rgba(255,255,255,.45);
                    border-radius: 0 0 1rem 1rem;
                    transform-origin: left;
                    animation: toastProgress 4.5s linear forwards;
                "></div>
            </div>
        @endforeach
    </div>
</div>

<style>
@keyframes toastProgress {
    from { transform: scaleX(1); }
    to   { transform: scaleX(0); }
}
.toast {
    transition: opacity .3s ease, transform .3s ease !important;
}
.toast:not(.show) {
    transform: translateY(-12px) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var toastEls = document.querySelectorAll('#toast-container .toast');
    toastEls.forEach(function (el) {
        var t = new bootstrap.Toast(el);
        t.show();
    });
});
</script>
@endif
