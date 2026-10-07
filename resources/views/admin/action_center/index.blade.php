@extends('layouts.admin')

@section('title', 'Admin Action Center - Management Priority Queue')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Admin Action Center"
        subtitle="Centralized operational queue highlighting pending verification, reviews, compliance, and urgent incidents requiring your attention."
        icon="bi-lightning-charge"
        :breadcrumbs="[
            ['label' => 'Management', 'url' => route('admin.dashboard')],
            ['label' => 'Action Center']
        ]"
    >
        <x-slot name="actions">
            @if($totalActionItems > 0)
                <span class="badge bg-warning-subtle text-warning-emphasis border px-3 py-2 fw-semibold">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $totalActionItems }} Items Need Attention
                </span>
            @else
                <span class="badge bg-success-subtle text-success border px-3 py-2 fw-semibold">
                    <i class="bi bi-check-circle me-1"></i>All Queues Up to Date
                </span>
            @endif
        </x-slot>
    </x-admin.page-header>

    {{-- Priority Metrics Row --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Property Reviews"
                :value="number_format($pendingPropertiesCount)"
                icon="bi-shield-check"
                variant="warning"
                subtext="Pending landlord property submissions"
                :url="route('admin.property_reviews.index')"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Student Verifications"
                :value="number_format($pendingStudentsCount)"
                icon="bi-person-vcard"
                variant="neutral"
                subtext="Students awaiting School ID validation"
                :url="route('admin.approvals.students')"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Document Reviews"
                :value="number_format($pendingDocumentsCount)"
                icon="bi-file-earmark-check"
                variant="neutral"
                subtext="Permits & safety certs submitted"
                :url="route('admin.documents.verification')"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Urgent Incidents"
                :value="number_format($urgentReportsCount)"
                icon="bi-flag"
                variant="danger"
                subtext="High priority unresolved cases"
                :url="route('admin.reports.index', ['status' => 'pending', 'priority' => 'high'])"
            />
        </div>
    </div>

    {{-- Urgent & Actionable Task Queues --}}
    <div class="row g-4">
        {{-- Urgent Incident Cases --}}
        <div class="col-12 col-lg-6">
            <div class="admin-table-card h-100">
                <div class="admin-card-header d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-shield-exclamation text-danger me-2"></i>Urgent Incident Reports</span>
                        <div class="small text-secondary">High priority student complaints requiring review</div>
                    </div>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">View Queue</a>
                </div>
                <div class="p-3">
                    @forelse($recentUrgentReports as $report)
                        <div class="p-3 mb-2 rounded-3 border bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark fs-6">{{ Str::limit($report->title, 45) }}</div>
                                <div class="small text-secondary">
                                    By <strong class="text-dark">{{ $report->user->full_name ?: $report->user->name }}</strong>
                                    &bull; {{ $report->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-brand">
                                Review <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                            No unresolved urgent incidents reported.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pending Property Submissions --}}
        <div class="col-12 col-lg-6">
            <div class="admin-table-card h-100">
                <div class="admin-card-header d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-buildings text-primary me-2"></i>Pending Property Reviews</span>
                        <div class="small text-secondary">Boarding house submissions awaiting review</div>
                    </div>
                    <a href="{{ route('admin.property_reviews.index') }}" class="btn btn-sm btn-outline-secondary">View Queue</a>
                </div>
                <div class="p-3">
                    @forelse($recentPendingProperties as $prop)
                        <div class="p-3 mb-2 rounded-3 border bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark fs-6">{{ $prop->name }}</div>
                                <div class="small text-secondary">
                                    Landlord: <strong class="text-dark">{{ $prop->landlord?->full_name ?: 'Landlord' }}</strong>
                                    &bull; {{ $prop->address }}
                                </div>
                            </div>
                            <a href="{{ route('admin.properties.inspect', $prop) }}" class="btn btn-sm btn-brand">
                                Inspect <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                            No pending property review submissions.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pending Student Verifications --}}
        <div class="col-12 col-lg-6">
            <div class="admin-table-card h-100">
                <div class="admin-card-header d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-person-vcard text-info me-2"></i>Student Verification Queue</span>
                        <div class="small text-secondary">Students awaiting School ID validation</div>
                    </div>
                    <a href="{{ route('admin.approvals.students') }}" class="btn btn-sm btn-outline-secondary">View Queue</a>
                </div>
                <div class="p-3">
                    @forelse($recentPendingStudents as $student)
                        <div class="p-3 mb-2 rounded-3 border bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark fs-6">{{ $student->full_name ?: $student->name }}</div>
                                <div class="small text-secondary">
                                    {{ $student->college ?: 'MinSU' }} &bull; {{ $student->program ?: 'Student' }}
                                </div>
                            </div>
                            <a href="{{ route('admin.approvals.students') }}" class="btn btn-sm btn-outline-secondary">
                                Verify <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                            No pending student verification requests.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pending Landlord Documents --}}
        <div class="col-12 col-lg-6">
            <div class="admin-table-card h-100">
                <div class="admin-card-header d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-file-earmark-check text-success me-2"></i>Landlord Document Review Queue</span>
                        <div class="small text-secondary">Permits and safety certifications awaiting accreditation</div>
                    </div>
                    <a href="{{ route('admin.documents.verification') }}" class="btn btn-sm btn-outline-secondary">View Queue</a>
                </div>
                <div class="p-3">
                    @forelse($recentPendingDocuments as $doc)
                        <div class="p-3 mb-2 rounded-3 border bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark fs-6">{{ $doc->typeLabel($doc->document_type) }}</div>
                                <div class="small text-secondary">
                                    Landlord: <strong class="text-dark">{{ $doc->landlord?->full_name ?: 'Landlord' }}</strong>
                                    &bull; Submitted {{ $doc->submitted_at?->diffForHumans() ?? 'recently' }}
                                </div>
                            </div>
                            <a href="{{ route('admin.documents.verification') }}" class="btn btn-sm btn-outline-secondary">
                                Review <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                            No pending landlord document submissions.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
