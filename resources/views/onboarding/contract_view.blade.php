@php
    $layout = match ($role ?? 'student') {
        'admin' => 'layouts.admin',
        'landlord' => 'layouts.landlord',
        default => 'layouts.student_dashboard',
    };

    $portalLabel = match ($role ?? 'student') {
        'admin' => 'Admin Portal',
        'landlord' => 'Landlord Portal',
        default => 'Student Portal',
    };

    $statusLabel = ucfirst(str_replace('_', ' ', (string) ($onboarding->status ?? 'pending')));
    $viewerNonce = now()->format('Uu');
    $pdfPreviewViewerBase = $pdfPreviewUrl . (str_contains($pdfPreviewUrl, '?') ? '&' : '?') . 'viewer=fit-width-' . $viewerNonce;
    $pdfViewerUrl = $pdfPreviewViewerBase . '#page=1&view=FitH&zoom=FitH,0,0&pagemode=none&navpanes=0&toolbar=1';
    $pdfOpenTabUrl = $pdfPreviewUrl . '#page=1&view=Fit&zoom=Fit,0,0&toolbar=1';
@endphp

@extends($layout)

@section('title', 'Onboarding Contract')

@section('content')
<div class="container-fluid py-2">
    <div class="contract-preview-shell">
        <div class="contract-preview-header mb-3">
            <div>
                <div class="text-uppercase small text-muted fw-semibold">{{ $portalLabel }}</div>
                <h2 class="h4 mb-1">Onboarding Contract PDF Preview</h2>
                <div class="text-muted small">Inline PDF viewer for onboarding #{{ $onboarding->id }}.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ $backUrl }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
                <a href="{{ $pdfOpenTabUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Open PDF
                </a>
                <a href="{{ $pdfDownloadUrl }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-filetype-pdf me-1"></i>Download PDF
                </a>
            </div>
        </div>

        <div class="preview-meta mb-3">
                <span class="meta-pill"><i class="bi bi-person me-1"></i>{{ $onboarding->booking->student->full_name ?? 'Student' }}</span>
                <span class="meta-pill"><i class="bi bi-building me-1"></i>{{ $onboarding->booking->room->property->name ?? 'Property' }}</span>
                <span class="meta-pill"><i class="bi bi-door-open me-1"></i>Room {{ $onboarding->booking->room->room_number ?? '-' }}</span>
                <span class="meta-pill"><i class="bi bi-clipboard-check me-1"></i>{{ $statusLabel }}</span>
        </div>

        <div class="pdf-frame-shell">
            <iframe
                src="{{ $pdfViewerUrl }}"
                class="pdf-frame"
                title="Onboarding Contract PDF"
            ></iframe>

            <div class="preview-fallback small text-muted">
                If the preview does not load, open it directly:
                <a href="{{ $pdfOpenTabUrl }}" target="_blank" rel="noopener">View PDF</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .contract-preview-shell {
        background: #eef2f6;
        border: 1px solid rgba(2, 8, 20, .1);
        border-radius: 1rem;
        padding: 1rem;
    }

    .contract-preview-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: .9rem;
        flex-wrap: wrap;
    }

    .preview-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        margin-bottom: .8rem;
    }

    .meta-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid rgba(2, 8, 20, .12);
        border-radius: 999px;
        background: #f8fafc;
        color: #0f172a;
        padding: .2rem .6rem;
        font-size: .78rem;
        font-weight: 600;
    }

    .pdf-frame-shell {
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: .9rem;
        box-shadow: 0 14px 30px rgba(2, 8, 20, .12);
        overflow: hidden;
    }

    .pdf-frame {
        display: block;
        width: 100%;
        height: calc(100vh - 280px);
        min-height: 640px;
        border: 0;
        background: #e2e8f0;
    }

    .preview-fallback {
        border-top: 1px solid #e2e8f0;
        padding: .55rem .75rem;
        background: #f8fafc;
    }

    @media (max-width: 767.98px) {
        .contract-preview-shell {
            padding: .7rem;
        }

        .pdf-frame {
            height: calc(100vh - 240px);
            min-height: 480px;
        }
    }
</style>
@endpush
