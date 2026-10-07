@extends('layouts.admin')

@section('title', 'Incident Report #' . $report->id . ' - Admin Management')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Incident Report Details"
        subtitle="Review student incident details, associated boarding house context, and update resolution progress."
        icon="bi-flag"
        :breadcrumbs="[
            ['label' => 'Management', 'url' => route('admin.dashboard')],
            ['label' => 'Incident Reports', 'url' => route('admin.reports.index')],
            ['label' => 'Report #' . $report->id]
        ]"
    >
        <x-slot name="actions">
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                <i class="bi bi-hash me-1"></i>{{ $report->id }}
            </span>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Reports
            </a>
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="admin-table-card mb-4">
                <div class="admin-card-header fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span>Incident Case Overview</span>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Subject / Title</label>
                            <div class="fw-bold text-dark fs-5">{{ $report->title }}</div>
                        </div>

                        <div class="col-sm-6">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Reporter (Student)</label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $report->user->full_name ?: $report->user->name }}</div>
                                    <div class="text-secondary small">{{ $report->user->email }}</div>
                                    @if($report->user->contact_number)
                                        <div class="text-secondary small">{{ $report->user->contact_number }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Accommodation Reference</label>
                            @if($activeBooking && $activeBooking->room && $activeBooking->room->property)
                                <div class="p-2 rounded-2 bg-light border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="fw-bold text-dark">{{ $activeBooking->room->property->name }}</span>
                                        <span class="badge bg-success-subtle text-success border">Active Stay</span>
                                    </div>
                                    <div class="text-secondary small">Room {{ $activeBooking->room->room_number }} · Check-in: {{ $activeBooking->check_in->format('M d, Y') }}</div>
                                    <div class="text-muted small">Landlord: {{ $activeBooking->room->property->landlord?->full_name ?: 'Landlord' }}</div>
                                </div>
                            @else
                                <div class="text-muted small p-2 bg-light rounded-2 border">
                                    <i class="bi bi-info-circle me-1"></i>No active stay at inspection time (General inquiry or past tenant)
                                </div>
                            @endif
                        </div>

                        <div class="col-sm-6">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Priority &amp; Status</label>
                            <div class="d-flex align-items-center gap-2">
                                @if($report->priority === 'high')
                                    <x-admin.status-badge status="rejected" label="Critical Priority" />
                                @elseif($report->priority === 'medium')
                                    <x-admin.status-badge status="pending" label="Moderate Priority" />
                                @else
                                    <x-admin.status-badge status="approved" label="Minor Priority" />
                                @endif

                                @if($report->status === 'pending')
                                    <x-admin.status-badge status="pending" label="Pending Review" />
                                @elseif($report->status === 'in_progress')
                                    <x-admin.status-badge status="info" label="In Progress" />
                                @else
                                    <x-admin.status-badge status="completed" label="Resolved" />
                                @endif
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Timeline</label>
                            <div class="small text-dark">Submitted: <strong>{{ $report->created_at->format('M d, Y h:i A') }}</strong> ({{ $report->created_at->diffForHumans() }})</div>
                            @if($report->resolved_at)
                                <div class="small text-success mt-1">Resolved: <strong>{{ $report->resolved_at->format('M d, Y h:i A') }}</strong></div>
                            @endif
                        </div>

                        <div class="col-12 mt-3">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Incident Description</label>
                            <div class="p-3 bg-light rounded-3 border text-dark" style="white-space: pre-line;">{{ $report->description }}</div>
                        </div>

                        @if(!empty($report->admin_response))
                            <div class="col-12 mt-2">
                                <label class="text-uppercase text-primary fw-semibold small tracking-wider mb-1">Current Administrative Response</label>
                                <div class="p-3 bg-primary-subtle rounded-3 border border-primary-subtle text-dark" style="white-space: pre-line;">{{ $report->admin_response }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="admin-table-card">
                <div class="admin-card-header fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-sliders2 text-primary"></i>
                    <span>Resolution &amp; Actions</span>
                </div>
                <div class="p-4">
                    <form action="{{ route('admin.reports.update', $report) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold text-dark small">Update Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" @selected($report->status === 'pending')>Pending Review</option>
                                <option value="in_progress" @selected($report->status === 'in_progress')>In Progress / Investigating</option>
                                <option value="resolved" @selected($report->status === 'resolved')>Resolved / Closed</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="admin_response" class="form-label fw-semibold text-dark small">Resolution Response / Notes</label>
                            <textarea
                                class="form-control"
                                id="admin_response"
                                name="admin_response"
                                rows="5"
                                placeholder="Explain findings, resolution actions, or instructions provided to the student..."
                            >{{ old('admin_response', $report->admin_response) }}</textarea>
                            <div class="form-text small text-muted mt-1">
                                Updating this response will notify the student in their dashboard and mark the response unread for them.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-brand w-100">
                            <i class="bi bi-check2-circle me-1"></i>Save &amp; Update Case
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection