@extends('layouts.admin')

@section('title', 'Document Monitoring')

@section('content')
<style>
    .stat-tile { border:1px solid rgba(2,8,20,.08); border-radius:1rem; background:#fff; padding:1rem 1.1rem; box-shadow:0 10px 26px rgba(2,8,20,.05); }
    .stat-ic { width:42px; height:42px; border-radius:.9rem; display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; }
    .sbadge { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.25rem .62rem; font-size:.73rem; font-weight:700; white-space:nowrap; }
    .sb-pending { background:#e0f2fe; color:#075985; }
    .sb-approved{ background:#dcfce7; color:#166534; }
    .sb-rejected{ background:#fee2e2; color:#991b1b; }
    .sb-valid   { background:#dcfce7; color:#166534; }
    .sb-soon    { background:#fef3c7; color:#92400e; }
    .sb-expired { background:#fee2e2; color:#991b1b; }
    .filter-pill { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.38rem .85rem; font-size:.82rem; font-weight:600; border:1px solid rgba(2,8,20,.14); text-decoration:none; color:#475569; background:#fff; }
    .filter-pill.active { background:#166534; color:#fff; border-color:#166534; }
    .doc-table { width:100%; border-collapse:collapse; font-size:.86rem; }
    .doc-table th { padding:.55rem .8rem; font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:rgba(2,8,20,.45); font-weight:600; border-bottom:1px solid rgba(2,8,20,.08); white-space:nowrap; text-align:left; }
    .doc-table td { padding:.65rem .8rem; border-bottom:1px solid rgba(2,8,20,.06); vertical-align:middle; }
    .doc-table tr:last-child td { border-bottom:none; }
    .doc-table tr:hover td { background:rgba(22,101,52,.025); }
</style>

<div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <div class="text-uppercase small text-secondary fw-semibold mb-1">Compliance</div>
        <h1 class="h4 mb-0 fw-bold"><i class="bi bi-activity me-1" style="color:#166534;"></i> Document Monitoring</h1>
        <p class="text-secondary small mb-0 mt-1">Monitor landlord document verification and expiration across all boarding houses.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.documents.verification') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
            <i class="bi bi-file-earmark-check me-1"></i>Verification Queue
        </a>
        @if($includeHistory)
            <a href="{{ route('admin.documents.monitoring', ['search' => $search, 'document_type' => $documentType, 'verification_status' => $statusFilter, 'expiration_status' => $expirationFilter]) }}" class="btn btn-sm btn-outline-success rounded-pill">
                <i class="bi bi-clock-history me-1"></i>Current Versions
            </a>
        @else
            <a href="{{ route('admin.documents.monitoring', ['search' => $search, 'document_type' => $documentType, 'verification_status' => $statusFilter, 'expiration_status' => $expirationFilter, 'history' => 1]) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-clock-history me-1"></i>Include History
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm py-2 mb-3">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm py-2 mb-3">{{ session('error') }}</div>
@endif

{{-- Summary statistics (from actual DB queries) --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-tile h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-ic" style="background:rgba(22,163,74,.12);border:1px solid rgba(22,163,74,.25);color:#15803d;"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="h4 mb-0">{{ $stats['valid'] }}</div>
                    <div class="small text-secondary">Valid</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-tile h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-ic" style="background:rgba(245,158,11,.14);border:1px solid rgba(245,158,11,.3);color:#b45309;"><i class="bi bi-clock-history"></i></div>
                <div>
                    <div class="h4 mb-0">{{ $stats['expiring_soon'] }}</div>
                    <div class="small text-secondary">Expiring Soon</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-tile h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-ic" style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.25);color:#b91c1c;"><i class="bi bi-x-octagon"></i></div>
                <div>
                    <div class="h4 mb-0">{{ $stats['expired'] }}</div>
                    <div class="small text-secondary">Expired</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-tile h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-ic" style="background:rgba(14,165,233,.12);border:1px solid rgba(14,165,233,.28);color:#0369a1;"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="h4 mb-0">{{ $stats['pending'] }}</div>
                    <div class="small text-secondary">Pending Verification</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.documents.monitoring') }}" class="d-flex flex-wrap gap-2 align-items-center mb-3">
    @if($includeHistory)
        <input type="hidden" name="history" value="1">
    @endif
    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm rounded-pill" style="width:220px;" placeholder="Search landlord...">
    <select name="document_type" class="form-select form-select-sm rounded-pill" style="width:auto;">
        <option value="">All document types</option>
        @foreach(\App\Models\LandlordDocument::types() as $type => $label)
            <option value="{{ $type }}" @selected($documentType === $type)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="verification_status" class="form-select form-select-sm rounded-pill" style="width:auto;">
        <option value="all">Any verification status</option>
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
            <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="expiration_status" class="form-select form-select-sm rounded-pill" style="width:auto;">
        <option value="all">Any expiration status</option>
        <option value="valid" @selected($expirationFilter === 'valid')>Valid</option>
        <option value="expiring_soon" @selected($expirationFilter === 'expiring_soon')>Expiring Soon</option>
        <option value="expired" @selected($expirationFilter === 'expired')>Expired</option>
    </select>
    <button type="submit" class="btn btn-sm btn-brand rounded-pill px-3"><i class="bi bi-funnel me-1"></i>Filter</button>
    <a href="{{ route('admin.documents.monitoring') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Reset</a>
</form>

<div class="bg-white border rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Landlord</th>
                    <th>Requirement</th>
                    <th>Document Number</th>
                    <th>Expiration Date</th>
                    <th>Verification</th>
                    <th>Expiration</th>
                    <th>Days Remaining</th>
                    <th class="text-end">Action</th>
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
                        $vBadge = match($document->verification_status) {
                            'approved' => 'sb-approved',
                            'rejected' => 'sb-rejected',
                            default => 'sb-pending',
                        };
                        $eBadge = match($expiration['status']) {
                            'valid' => 'sb-valid',
                            'expiring_soon' => 'sb-soon',
                            'expired' => 'sb-expired',
                            default => 'sb-pending',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $document->landlord?->full_name ?: 'Unknown' }}</div>
                            <div class="small text-secondary">{{ $document->landlord?->email }}</div>
                        </td>
                        <td class="fw-semibold">{{ $document->typeLabel($document->document_type) }}</td>
                        <td class="text-secondary">{{ $document->document_number ?: '—' }}</td>
                        <td>{{ $document->expiration_date?->format('M d, Y') ?: '—' }}</td>
                        <td><span class="sbadge {{ $vBadge }}">{{ ucfirst($document->verification_status) }}</span></td>
                        <td><span class="sbadge {{ $eBadge }}">{{ ucwords(str_replace('_', ' ', $expiration['status'])) }}</span></td>
                        <td class="text-secondary">{{ $daysLabel }}</td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap justify-content-end gap-1">
                                <a href="{{ file_download_url($document->file_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                                <a href="{{ file_download_url($document->file_path, true) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">No documents match the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($documents->hasPages())
    <div class="bg-white border rounded-4 shadow-sm px-3 py-3 mt-3">{{ $documents->links() }}</div>
@endif
@endsection
