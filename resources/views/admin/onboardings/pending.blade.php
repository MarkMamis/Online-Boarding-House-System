@extends('layouts.admin')

@section('title', 'Pending Document Onboardings')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Pending Document Onboardings"
        subtitle="Students waiting for document submissions before onboarding can proceed."
        icon="bi-hourglass-split"
        :breadcrumbs="[
            ['label' => 'Monitoring', 'url' => route('admin.dashboard')],
            ['label' => 'Onboarding Pipeline', 'url' => route('admin.onboardings.index')],
            ['label' => 'Pending Documents']
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
            <a href="{{ route('admin.onboardings.active') }}" class="filter-chip">Active</a>
            <a href="{{ route('admin.onboardings.pending') }}" class="filter-chip active">Pending</a>
            <a href="{{ route('admin.onboardings.completed') }}" class="filter-chip">Completed</a>
        </div>
    </div>

    <div class="admin-table-card">
        <div class="admin-card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark"><i class="bi bi-list-ul me-1 text-primary"></i> Pending Document Records</span>
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
                        <th>Waiting Since</th>
                        <th class="pe-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onboardings as $onboarding)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning fw-bold" style="width: 34px; height: 34px; font-size: 0.82rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $onboarding->booking->student->full_name }}</div>
                                        <div class="text-secondary small">{{ $onboarding->booking->student->student_id }}</div>
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
                                <div class="small fw-medium text-dark">{{ $onboarding->created_at->format('M d, Y') }}</div>
                                <div class="text-secondary small">{{ $onboarding->created_at->diffForHumans() }}</div>
                                @if($onboarding->created_at->diffInDays() > 7)
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-2 mt-1">Overdue</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('admin.onboardings.show', $onboarding) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state
                                    title="No pending document onboardings"
                                    description="All tenant onboardings have submitted required documents."
                                    icon="bi-check2-circle"
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