@extends('layouts.landlord')

@section('title', 'Tenant Move-in Management')

@section('content')
<div class="landlord-page-shell">
    @php
        $pendingCount = $onboardings->where('status', 'pending')->count();
        $docsUploadedCount = $onboardings->where('status', 'documents_uploaded')->count();
        $inProgressCount = $onboardings->whereIn('status', ['contract_signed', 'deposit_paid'])->count();
        $completedCount = $onboardings->where('status', 'completed')->count();
        $actionRequiredCount = $docsUploadedCount + $onboardings->where('status', 'deposit_paid')->count();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="landlord-header-eyebrow">Tenant Pipeline</div>
            <h1 class="landlord-header-title mb-1">Tenant Move-in Management</h1>
            <div class="landlord-header-subtitle">Track incoming resident onboarding progress from document upload to completed move-in.</div>
        </div>
        @if($onboardings->isNotEmpty())
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="landlord-summary-strip">
                    <span class="landlord-summary-dot"></span>
                    <span><strong>{{ $onboardings->count() }}</strong> {{ Str::plural('Move-in', $onboardings->count()) }}</span>
                    @if($actionRequiredCount > 0)
                        <span class="text-muted">·</span>
                        <span class="text-warning-emphasis fw-bold">{{ $actionRequiredCount }} Action Required</span>
                    @endif
                    <span class="text-muted">·</span>
                    <span class="text-success fw-bold">{{ $completedCount }}</span> Completed
                </span>
            </div>
        @endif
    </div>

    @if($onboardings->isEmpty())
        <div class="landlord-empty-state">
            <div class="landlord-empty-icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <div class="landlord-empty-title">No Move-ins in Progress</div>
            <div class="landlord-empty-text">When student boarding requests are approved, their onboarding move-in requirements will appear here for review.</div>
        </div>
    @else
        <div class="landlord-card">
            @foreach($onboardings as $onboarding)
                @php
                    $status = strtolower((string) $onboarding->status);
                    $progress = match($status) {
                        'pending' => 25,
                        'documents_uploaded' => 50,
                        'contract_signed' => 75,
                        'deposit_paid' => 90,
                        'completed' => 100,
                        default => 0,
                    };

                    $statusBadgeClass = match($status) {
                        'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                        'documents_uploaded' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                        'contract_signed' => 'bg-primary-subtle text-primary border border-primary-subtle',
                        'deposit_paid' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                        'completed' => 'bg-success-subtle text-success border border-success-subtle',
                        default => 'bg-light text-dark',
                    };

                    $statusLabel = match($status) {
                        'pending' => 'Waiting for Student Upload',
                        'documents_uploaded' => 'Review Documents',
                        'contract_signed' => 'Contract Signed',
                        'deposit_paid' => 'Payment Under Review',
                        'completed' => 'Move-in Complete',
                        default => ucfirst(str_replace('_', ' ', $status)),
                    };

                    $statusNote = match($status) {
                        'pending' => 'Student has not submitted required documents yet',
                        'documents_uploaded' => 'Documents submitted · Ready for your review',
                        'contract_signed' => 'Contract signed · Awaiting deposit payment',
                        'deposit_paid' => 'Deposit payment submitted · Confirm payment to finalize',
                        'completed' => 'All requirements verified · Stay is active',
                        default => 'Progress status unavailable',
                    };

                    $hasDocuments = is_array($onboarding->uploaded_documents ?? null) && count($onboarding->uploaded_documents) > 0;
                    $needsPaymentReview = $status === 'deposit_paid' && empty($onboarding->deposit_paid_at);
                    $isActionPrimary = (in_array($status, ['pending', 'documents_uploaded'], true) && $hasDocuments) || $needsPaymentReview;
                    $actionLabel = $needsPaymentReview ? 'Review Payment' : ($isActionPrimary ? 'Review Documents' : 'View Details');
                    $actionClass = $isActionPrimary ? 'btn-brand' : 'btn-outline-secondary';
                @endphp

                <article class="onboarding-item">
                    <div class="onboarding-main">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <div class="student-avatar-sm">
                                {{ strtoupper(substr($onboarding->booking->student->full_name ?? 'S', 0, 1)) }}
                            </div>
                            <span class="fw-bold text-dark fs-6">{{ $onboarding->booking->student->full_name }}</span>
                            <span class="text-muted small">({{ $onboarding->booking->student->email }})</span>
                            <span class="badge rounded-pill {{ $statusBadgeClass }} small ms-auto d-md-none">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="onboarding-meta-row mb-2">
                            <span class="meta-chip"><i class="bi bi-building"></i>{{ $onboarding->booking->room->property->name }}</span>
                            <span class="meta-chip"><i class="bi bi-door-open"></i>Room {{ $onboarding->booking->room->room_number }}</span>
                            <span class="meta-chip"><i class="bi bi-calendar-range"></i>{{ optional($onboarding->booking->check_in)->format('M d, Y') }} - {{ optional($onboarding->booking->check_out)->format('M d, Y') }}</span>
                            <span class="text-muted small ms-1"><i class="bi bi-clock-history me-1"></i>Updated {{ $onboarding->updated_at->diffForHumans() }}</span>
                        </div>

                        <div class="progress-wrap" style="max-width: 380px;">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Onboarding Progress</span>
                                <span class="fw-bold text-dark">{{ $progress }}%</span>
                            </div>
                            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: {{ $progress }}%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="onboarding-side">
                        <div class="status-panel d-none d-md-block">
                            <span class="badge rounded-pill {{ $statusBadgeClass }} px-2 py-1">
                                {{ $statusLabel }}
                            </span>
                            <div class="status-note">{{ $statusNote }}</div>
                        </div>

                        <div class="onboarding-actions">
                            <a href="{{ route('landlord.onboarding.review', $onboarding) }}" class="btn btn-sm {{ $actionClass }} rounded-pill px-3">
                                {{ $actionLabel }} <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .onboarding-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: start;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
        transition: background .2s ease;
    }
    .onboarding-item:hover {
        background: #fbfcfd;
    }
    .onboarding-item:last-child {
        border-bottom: none;
    }
    .student-avatar-sm {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: rgba(20,83,45,.15);
        color: #14532d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .72rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .onboarding-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        align-items: center;
    }
    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid rgba(2,8,20,.1);
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        padding: .15rem .55rem;
        font-size: .76rem;
        font-weight: 600;
    }
    .onboarding-side {
        display: grid;
        gap: .6rem;
        min-width: 200px;
        justify-items: end;
    }
    .status-panel {
        text-align: right;
    }
    .status-note {
        margin-top: .25rem;
        font-size: .73rem;
        color: #64748b;
    }
    .onboarding-actions {
        display: inline-flex;
        gap: .45rem;
        align-items: center;
        justify-content: flex-end;
    }

    @media (max-width: 767.98px) {
        .onboarding-item {
            grid-template-columns: 1fr;
        }
        .onboarding-side {
            justify-items: start;
            min-width: 0;
            width: 100%;
            border-top: 1px dashed rgba(2,8,20,.08);
            padding-top: .75rem;
        }
        .onboarding-actions {
            width: 100%;
        }
        .onboarding-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush
