@extends('layouts.admin')

@section('title', 'Institutional Reports - OBHS')

@section('content')
<div class="admin-page-shell">
    {{-- Page Header --}}
    <x-admin.page-header
        title="Institutional Reports"
        subtitle="Generate, review, print, and export official MinSU student accommodation and property reports."
        icon="bi-journal-text"
        :breadcrumbs="[
            ['label' => 'Insights', 'url' => route('admin.dashboard')],
            ['label' => 'Institutional Reports']
        ]"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.analytics.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-graph-up-arrow me-1"></i>Analytics Dashboard
            </a>
            <a href="{{ route('admin.institutional_reports.export', array_merge(request()->query(), ['report_preset' => $selectedPreset])) }}" class="btn btn-sm btn-brand">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.institutional_reports.print', array_merge(request()->query(), ['report_preset' => $selectedPreset])) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>Print Report
            </a>
        </x-slot>
    </x-admin.page-header>

    {{-- Institutional Report Presets Selector Bar --}}
    <div class="admin-toolbar mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <span class="small fw-bold text-uppercase text-secondary tracking-wider">
                <i class="bi bi-journal-bookmark me-1 text-primary"></i>Select Institutional Report Preset
            </span>
            <span class="badge bg-light text-dark border">
                <i class="bi bi-table me-1 text-secondary"></i>{{ $presetData['rows']->count() }} Records in Preview
            </span>
        </div>
        <div class="row g-2">
            @foreach($reportPresets as $key => $preset)
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="{{ route('admin.institutional_reports.index', array_merge(request()->query(), ['report_preset' => $key])) }}"
                       class="card h-100 text-decoration-none border p-3 rounded-3 transition-all {{ $selectedPreset === $key ? 'bg-primary-subtle border-primary shadow-sm' : 'bg-white hover-shadow' }}">
                        <div class="d-flex align-items-start gap-3">
                            <div class="p-2 rounded-2 {{ $selectedPreset === $key ? 'bg-primary text-white' : 'bg-light text-primary' }}">
                                <i class="bi {{ $preset['icon'] }} fs-5"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="fw-bold mb-1 text-truncate {{ $selectedPreset === $key ? 'text-primary' : 'text-dark' }}">
                                        {{ $preset['title'] }}
                                    </h6>
                                    @if($selectedPreset === $key)
                                        <span class="badge bg-primary text-white small ms-1">Active</span>
                                    @endif
                                </div>
                                <p class="small text-muted mb-0 line-clamp-2" style="font-size: 0.78rem;">
                                    {{ $preset['description'] }}
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="admin-toolbar mb-4">
        <form method="GET" action="{{ route('admin.institutional_reports.index') }}" id="reportsFilterForm">
            <input type="hidden" name="report_preset" value="{{ $selectedPreset }}">
            <div class="row g-3 align-items-end">
                {{-- Period Preset --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="period_preset" class="form-label small fw-semibold text-secondary mb-1">Reporting Period</label>
                    <select name="period_preset" id="period_preset" class="form-select" onchange="toggleCustomDates(this.value)">
                        <option value="all" @selected(($periodInfo['periodPreset'] ?? '') === 'all')>All Time (Current Active)</option>
                        <option value="this_month" @selected(($periodInfo['periodPreset'] ?? '') === 'this_month')>This Month ({{ now()->format('M Y') }})</option>
                        <option value="last_month" @selected(($periodInfo['periodPreset'] ?? '') === 'last_month')>Last Month ({{ now()->subMonthNoOverflow()->format('M Y') }})</option>
                        <option value="custom" @selected(($periodInfo['periodPreset'] ?? '') === 'custom')>Custom Date Range</option>
                    </select>
                </div>

                {{-- Custom Date From --}}
                <div class="col-6 col-sm-3 col-md-2 custom-date-col {{ ($periodInfo['periodPreset'] ?? '') === 'custom' ? '' : 'd-none' }}" id="customDateFromCol">
                    <label for="date_from" class="form-label small fw-semibold text-secondary mb-1">From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $periodInfo['dateFrom'] ?? '' }}" class="form-control">
                </div>

                {{-- Custom Date To --}}
                <div class="col-6 col-sm-3 col-md-2 custom-date-col {{ ($periodInfo['periodPreset'] ?? '') === 'custom' ? '' : 'd-none' }}" id="customDateToCol">
                    <label for="date_to" class="form-label small fw-semibold text-secondary mb-1">To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $periodInfo['dateTo'] ?? '' }}" class="form-control">
                </div>

                {{-- Property Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="property_id" class="form-label small fw-semibold text-secondary mb-1">Boarding House</label>
                    <select name="property_id" id="property_id" class="form-select">
                        <option value="">All Boarding Houses</option>
                        @foreach($filterOptions['boardingHouses'] as $bh)
                            <option value="{{ $bh->id }}" @selected((int)$propertyId === (int)$bh->id)>{{ $bh->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- College Filter --}}
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="college" class="form-label small fw-semibold text-secondary mb-1">College</label>
                    <select name="college" id="college" class="form-select">
                        <option value="">All Colleges</option>
                        @foreach($filterOptions['colleges'] as $col)
                            <option value="{{ $col['code'] }}" @selected($college === $col['code'])>{{ $col['code'] }} - {{ \Illuminate\Support\Str::limit($col['name'], 20) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="col-12 col-sm-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-brand flex-fill">
                        <i class="bi bi-funnel me-1"></i>Apply
                    </button>
                    <a href="{{ route('admin.institutional_reports.index', ['report_preset' => $selectedPreset]) }}" class="btn btn-action-icon" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>

            @if(!empty($periodInfo['periodLabel']))
                <div class="mt-2 text-muted small d-flex align-items-center gap-1">
                    <i class="bi bi-calendar3 text-primary"></i>
                    Active Period Filter: <strong class="text-dark">{{ $periodInfo['periodLabel'] }}</strong>
                </div>
            @endif
        </form>
    </div>

    {{-- Report Content Preview Card --}}
    <div class="admin-table-card mb-4">
        <div class="admin-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi {{ $reportPresets[$selectedPreset]['icon'] ?? 'bi-file-earmark-text' }} me-2 text-primary"></i>
                    {{ $reportPresets[$selectedPreset]['title'] ?? 'Institutional Report Data' }}
                </h6>
                <span class="small text-secondary">{{ $reportPresets[$selectedPreset]['description'] ?? '' }}</span>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                @foreach(($presetData['summary'] ?? []) as $sLabel => $sVal)
                    <span class="badge bg-light text-dark border fw-normal py-2 px-3">
                        <span class="text-secondary">{{ $sLabel }}:</span> <strong class="text-dark">{{ $sVal }}</strong>
                    </span>
                @endforeach
                <a href="{{ route('admin.institutional_reports.export', array_merge(request()->query(), ['report_preset' => $selectedPreset])) }}" class="btn btn-sm btn-outline-brand ms-2">
                    <i class="bi bi-download me-1"></i>Download CSV
                </a>
                <a href="{{ route('admin.institutional_reports.print', array_merge(request()->query(), ['report_preset' => $selectedPreset])) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i>Print View
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        @foreach($presetData['columns'] as $colIndex => $colName)
                            <th class="{{ $colIndex === 0 ? 'ps-4' : '' }}">{{ $colName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($presetData['rows'] as $pRow)
                        <tr>
                            @foreach((is_array($pRow) ? $pRow : (array) $pRow) as $valIndex => $val)
                                <td class="{{ $loop->first ? 'ps-4 fw-semibold text-dark' : '' }}">{{ $val }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($presetData['columns']) }}">
                                <x-admin.empty-state
                                    title="No report records found"
                                    description="No data matches the selected preset and filtering criteria."
                                    icon="bi-journal-x"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleCustomDates(preset) {
        const fromCol = document.getElementById('customDateFromCol');
        const toCol = document.getElementById('customDateToCol');
        if (preset === 'custom') {
            fromCol.classList.remove('d-none');
            toCol.classList.remove('d-none');
        } else {
            fromCol.classList.add('d-none');
            toCol.classList.add('d-none');
        }
    }
</script>
@endsection
