@extends('layouts.admin')

@section('title', 'Compliance Monitor - Admin Panel')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Compliance Monitor"
        subtitle="Monitor document validity and compliance lifecycle across registered boarding houses."
        icon="bi-activity"
        :breadcrumb="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Monitoring'],
            ['label' => 'Compliance Monitor']
        ]"
    />

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm py-2 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm py-2 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Summary Statistics Metric Strip --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <x-admin.metric-card
                label="Valid Documents"
                :value="number_format($stats['valid'] ?? 0)"
                variant="success"
                icon="bi-check-circle"
            />
        </div>
        <div class="col-6 col-md-3">
            <x-admin.metric-card
                label="Expiring Soon"
                :value="number_format($stats['expiring_soon'] ?? 0)"
                variant="warning"
                icon="bi-clock-history"
            />
        </div>
        <div class="col-6 col-md-3">
            <x-admin.metric-card
                label="Expired"
                :value="number_format($stats['expired'] ?? 0)"
                variant="danger"
                icon="bi-x-octagon"
            />
        </div>
        <div class="col-6 col-md-3">
            <x-admin.metric-card
                label="Pending Review"
                :value="number_format($stats['pending'] ?? 0)"
                variant="info"
                icon="bi-hourglass-split"
            />
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="admin-toolbar mb-4">
        <form method="GET" action="{{ route('admin.documents.monitoring') }}" class="row g-2 align-items-center">
            @if($includeHistory)
                <input type="hidden" name="history" value="1">
            @endif
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Search landlord...">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="document_type" class="form-select form-select-sm">
                    <option value="">All document types</option>
                    @foreach(\App\Models\LandlordDocument::types() as $type => $label)
                        <option value="{{ $type }}" @selected($documentType === $type)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="verification_status" class="form-select form-select-sm">
                    <option value="all">Any verification</option>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="expiration_status" class="form-select form-select-sm">
                    <option value="all">Any expiration</option>
                    <option value="valid" @selected($expirationFilter === 'valid')>Valid</option>
                    <option value="expiring_soon" @selected($expirationFilter === 'expiring_soon')>Expiring Soon</option>
                    <option value="expired" @selected($expirationFilter === 'expired')>Expired</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-brand px-3"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('admin.documents.monitoring') }}" class="btn btn-sm btn-light border px-3 text-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="admin-table-card">
        <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success"></i>
                <span>Monitored Compliance Records</span>
            </div>
            <span class="badge rounded-pill bg-light text-secondary border">{{ $documents->total() }} records</span>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="ps-3">Landlord</th>
                        <th>Requirement</th>
                        <th>Document Number</th>
                        <th>Expiration Date</th>
                        <th>Verification</th>
                        <th>Expiration</th>
                        <th>Days Remaining</th>
                        <th class="pe-3 text-end" style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        @php
                            $expiration = $document->expirationInfo();
                            $days = $expiration['days_remaining'];
                            $daysLabel = $days === null
                                ? '—'
                                : ($days <= 0 ? abs($days) . ' day' . (abs($days) === 1 ? '' : 's') . ' ago' : $days . ' day' . ($days === 1 ? '' : 's'));
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $document->landlord?->full_name ?: 'Unknown' }}</div>
                                <div class="small text-muted">{{ $document->landlord?->email }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    <i class="bi bi-file-earmark-text me-1 text-primary"></i>{{ $document->typeLabel($document->document_type) }}
                                </div>
                            </td>
                            <td class="text-secondary small">{{ $document->document_number ?: '—' }}</td>
                            <td>{{ $document->expiration_date?->format('M d, Y') ?: '—' }}</td>
                            <td>
                                <x-admin.status-badge :status="$document->verification_status" />
                            </td>
                            <td>
                                <x-admin.status-badge :status="$expiration['status']" />
                            </td>
                            <td class="text-secondary small">{{ $daysLabel }}</td>
                            <td class="pe-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" onclick="openDocumentPreview('{{ file_download_url($document->file_path) }}', '{{ addslashes($document->typeLabel($document->document_type)) }} - {{ addslashes(optional($document->landlord)->full_name ?? 'Landlord') }}')" class="btn btn-sm btn-light border btn-action-icon text-secondary" title="View Document">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ file_download_url($document->file_path, true) }}" download class="btn btn-sm btn-light border btn-action-icon text-secondary" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-admin.empty-state
                                    icon="bi-shield-check"
                                    title="No compliance records match filters"
                                    message="Try adjusting the filters above to view compliance documents."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="admin-card-header border-top d-flex justify-content-center">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

