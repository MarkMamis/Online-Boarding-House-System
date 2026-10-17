@extends('layouts.admin')

@section('title', 'Active Tenant Onboardings')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Active Tenant Onboardings"
        subtitle="Students currently progressing through active onboarding milestones."
        icon="bi-clipboard-check"
        :breadcrumbs="[
            ['label' => 'Monitoring', 'url' => route('admin.dashboard')],
            ['label' => 'Onboarding Pipeline', 'url' => route('admin.onboardings.index')],
            ['label' => 'Active']
        ]"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.onboardings.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>All Pipeline
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="admin-toolbar mb-4">
        <div class="d-flex flex-wrap gap-1">
            <a href="{{ route('admin.onboardings.index') }}" class="filter-chip">All</a>
            <a href="{{ route('admin.onboardings.active') }}" class="filter-chip active">Active</a>
            <a href="{{ route('admin.onboardings.pending') }}" class="filter-chip">Pending</a>
            <a href="{{ route('admin.onboardings.completed') }}" class="filter-chip">Completed</a>
        </div>
    </div>

    <div class="admin-table-card">
        <div class="admin-card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark"><i class="bi bi-list-ul me-1 text-primary"></i> Active Onboardings</span>
            <span class="badge bg-light text-secondary border fw-normal">{{ $onboardings->total() }} records</span>
        </div>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>Property</th>
                        <th>Landlord</th>
                        <th>Room</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th class="pe-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onboardings as $onboarding)
                        @php
                            $progress = match($onboarding->status) {
                                'pending' => 20,
                                'documents_uploaded' => 40,
                                'documents_approved' => 60,
                                'contract_signed' => 80,
                                'deposit_paid' => 100,
                                default => 0
                            };
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="width: 34px; height: 34px; font-size: 0.82rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $onboarding->booking->student->full_name }}</div>
                                        <div class="text-secondary small">{{ $onboarding->booking->student->student_id ?: 'Not provided' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $onboarding->booking->room->property->name }}</div>
                                <div class="text-secondary small">{{ Str::limit($onboarding->booking->room->property->address, 30) }}</div>
                            </td>
                            <td>
                                <div>{{ $onboarding->booking->room->property->landlord->full_name }}</div>
                                <div class="text-secondary small">{{ $onboarding->booking->room->property->landlord->email }}</div>
                            </td>
                            <td>
                                <div><span class="badge bg-light text-dark border">{{ $onboarding->booking->room->room_number }}</span></div>
                                <div class="text-secondary small mt-1">PHP {{ number_format($onboarding->booking->room->price, 2) }}/mo</div>
                            </td>
                            <td>
                                <x-admin.status-badge :status="$onboarding->status" />
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="min-width: 100px;">
                                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 999px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="small text-muted">{{ $progress }}%</span>
                                </div>
                            </td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('admin.onboardings.show', $onboarding) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state
                                    title="No active onboardings"
                                    description="There are currently no active tenant onboarding records."
                                    icon="bi-clipboard-check"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($onboardings->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $onboardings->links() }}
        </div>
    @endif
</div>
@endsection