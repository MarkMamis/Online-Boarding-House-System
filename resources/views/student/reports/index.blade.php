@extends('layouts.student_dashboard')

@section('title', 'My Reports')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-semibold mb-0">My Reports</h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-brand rounded-pill px-3" data-open-report-form="1">New report</button>
        </div>
    </div>

    <div id="newReportFormWrap" class="border rounded-4 bg-white shadow-sm p-4 mb-3" style="display:none;">
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
                <div class="border rounded-4 bg-white shadow-sm p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="fw-semibold">{{ $r->title }}</div>
                        <div>
                            <span class="badge text-bg-light">{{ $r->status }}</span>
                            @if(!empty($r->admin_response) && empty($r->response_read))
                                <span class="badge text-bg-danger ms-1">New response</span>
                            @endif
                        </div>
                    </div>
                    <div class="small text-muted">{{ $r->created_at?->diffForHumans() ?? '' }}</div>
                    @if(!empty($r->admin_response))
                        <div class="small mt-2"><strong>Response:</strong> {{ \Illuminate\Support\Str::limit($r->admin_response, 160) }}</div>
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
