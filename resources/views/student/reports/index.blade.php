@extends('layouts.student_dashboard')

@section('title', 'My Reports')

@push('styles')
<style>
    .reports-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .reports-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .7rem;
        margin-top: 1rem;
    }
    .reports-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .85rem;
        padding: .65rem .75rem;
    }
    .reports-summary-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(2,8,20,.5);
        font-weight: 700;
        margin-bottom: .18rem;
    }
    .reports-summary-value {
        font-size: 1rem;
        font-weight: 700;
        color: #14532d;
    }
    .reports-form-wrap {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 18px rgba(2,8,20,.05);
        padding: 1rem;
    }
    .report-card {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .95rem;
        background: #fff;
        box-shadow: 0 8px 18px rgba(2,8,20,.05);
        padding: .9rem;
    }
    .report-card.has-new-response {
        border-left: 4px solid #ef4444;
        padding-left: .7rem;
        background: linear-gradient(180deg, rgba(254,242,242,.25), #fff);
    }
    .report-response-box {
        border: 1px solid rgba(22,101,52,.18);
        border-left: 3px solid var(--brand);
        border-radius: .7rem;
        background: rgba(22,101,52,.05);
        padding: .65rem .75rem;
        margin-top: .6rem;
    }
    @media (max-width: 991.98px) {
        .reports-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .reports-shell {
            padding: .95rem;
        }
    }
</style>
@endpush

@section('content')
@php
    $reportItems = $reports instanceof \Illuminate\Pagination\AbstractPaginator ? $reports->getCollection() : collect($reports ?? []);
    $totalReports = (int) $reportItems->count();
    $openReports = (int) $reportItems->whereIn('status', ['open', 'pending', 'in_progress'])->count();
    $resolvedReports = (int) $reportItems->whereIn('status', ['resolved', 'closed'])->count();
    $newResponses = (int) $reportItems->filter(function ($r) {
        return !empty($r->admin_response) && empty($r->response_read);
    })->count();
@endphp

<div class="reports-shell mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Student Support</div>
            <h1 class="h3 mb-1">My Reports</h1>
            <div class="text-muted small">Submit concerns and track admin responses in one place.</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-brand rounded-pill px-3" data-open-report-form="1">New report</button>
        </div>
    </div>

    <div class="reports-summary mb-3">
        <div class="reports-summary-item">
            <div class="reports-summary-label">Total</div>
            <div class="reports-summary-value">{{ $totalReports }}</div>
        </div>
        <div class="reports-summary-item">
            <div class="reports-summary-label">Open</div>
            <div class="reports-summary-value">{{ $openReports }}</div>
        </div>
        <div class="reports-summary-item">
            <div class="reports-summary-label">Resolved</div>
            <div class="reports-summary-value">{{ $resolvedReports }}</div>
        </div>
        <div class="reports-summary-item">
            <div class="reports-summary-label">New Responses</div>
            <div class="reports-summary-value">{{ $newResponses }}</div>
        </div>
    </div>

    <div id="newReportFormWrap" class="reports-form-wrap mb-3" style="display:none;">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="fw-semibold">Submit a new report</div>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-close-report-form="1">Close</button>
        </div>
        <div class="small text-muted mb-3">Use this to report issues/concerns. An admin will respond.</div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Please fix the following:</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('student.reports.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <label class="form-label small text-muted">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g., Broken lock / Noise complaint" required>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label small text-muted">Priority</label>
                    <select name="priority" class="form-select" required>
                        <option value="low" @selected(old('priority', 'medium')==='low')>Low</option>
                        <option value="medium" @selected(old('priority', 'medium')==='medium')>Medium</option>
                        <option value="high" @selected(old('priority', 'medium')==='high')>High</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small text-muted">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Describe the issue clearly…" required>{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-brand rounded-pill px-4">Submit report</button>
            </div>
        </form>
    </div>

    <div class="row g-3">
        @forelse(($reports ?? collect()) as $r)
            <div class="col-12">
                <div class="report-card {{ !empty($r->admin_response) && empty($r->response_read) ? 'has-new-response' : '' }}">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-semibold">{{ $r->title }}</div>
                            <div class="small text-muted">{{ $r->created_at?->format('M d, Y h:i A') ?? '' }}</div>
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-wrap justify-content-end">
                            <span class="badge rounded-pill text-bg-light border">{{ $r->status }}</span>
                            @if(!empty($r->priority))
                                <span class="badge rounded-pill {{ $r->priority === 'high' ? 'text-bg-danger' : ($r->priority === 'medium' ? 'text-bg-warning' : 'text-bg-secondary') }}">{{ ucfirst($r->priority) }}</span>
                            @endif
                            @if(!empty($r->admin_response) && empty($r->response_read))
                                <span class="badge text-bg-danger ms-1">New response</span>
                            @endif
                        </div>
                    </div>

                    @if(!empty($r->description))
                        <div class="small mt-2 text-muted">{{ \Illuminate\Support\Str::limit($r->description, 180) }}</div>
                    @endif

                    @if(!empty($r->admin_response))
                        <div class="report-response-box">
                            <div class="small fw-semibold mb-1">Admin response</div>
                            <div class="small">{{ \Illuminate\Support\Str::limit($r->admin_response, 220) }}</div>

                            @if(empty($r->response_read))
                                <form method="POST" action="{{ route('student.reports.mark_read', $r->id) }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">Mark response as read</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-secondary mb-0">No reports yet.</div></div>
        @endforelse
    </div>

    @if(!empty($reports) && method_exists($reports, 'links'))
        <div class="mt-3">
            {{ $reports->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const reportFormWrap = document.getElementById('newReportFormWrap');
    const openReportForm = () => {
        if (!reportFormWrap) return;
        reportFormWrap.style.display = '';
        const titleInput = reportFormWrap.querySelector('input[name="title"]');
        if (titleInput) {
            setTimeout(() => titleInput.focus(), 50);
        }
    };
    const closeReportForm = () => {
        if (!reportFormWrap) return;
        reportFormWrap.style.display = 'none';
    };

    document.querySelectorAll('[data-open-report-form]').forEach(btn => {
        btn.addEventListener('click', () => openReportForm());
    });
    document.querySelectorAll('[data-close-report-form]').forEach(btn => {
        btn.addEventListener('click', () => closeReportForm());
    });

    @if($errors->any())
        openReportForm();
    @endif
</script>
@endpush
