@extends('layouts.landlord')

@section('content')
<div class="analytics-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Finance & Occupancy</div>
            <h1 class="h3 mb-1">Analytics & Insights</h1>
            <div class="text-muted small">Understand occupancy trends, booking performance, and portfolio-level room utilization.</div>
        </div>
        <span class="updated-chip"><i class="bi bi-clock-history"></i>Updated {{ now()->format('M j, Y g:i A') }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4">{{ session('success') }}</div>
    @endif

    <div class="analytics-summary mb-4">
        <div class="analytics-summary-item">
            <div class="analytics-summary-label">Total Rooms</div>
            <div class="analytics-summary-value">{{ $totalRooms }}</div>
        </div>
        <div class="analytics-summary-item">
            <div class="analytics-summary-label">Occupied</div>
            <div class="analytics-summary-value text-success-emphasis">{{ $occupiedRooms }}</div>
        </div>
        <div class="analytics-summary-item">
            <div class="analytics-summary-label">Available</div>
            <div class="analytics-summary-value text-primary-emphasis">{{ $availableRooms }}</div>
        </div>
        <div class="analytics-summary-item">
            <div class="analytics-summary-label">Maintenance</div>
            <div class="analytics-summary-value text-warning-emphasis">{{ $maintenanceRooms }}</div>
        </div>
        <div class="analytics-summary-item">
            <div class="analytics-summary-label">Monthly Revenue (30d)</div>
            <div class="analytics-summary-value text-success-emphasis">PHP {{ number_format($monthlyRevenue, 2) }}</div>
        </div>
        <div class="analytics-summary-item">
            <div class="analytics-summary-label">Expected Revenue</div>
            <div class="analytics-summary-value text-primary-emphasis">PHP {{ number_format($totalExpected, 2) }}</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-5">
            <div class="analytics-card h-100">
                <div class="analytics-card-header">
                    <h5 class="mb-0">Occupancy Rate</h5>
                </div>
                <div class="analytics-card-body">
                    <div class="occupancy-wrap">
                        <div class="progress-label-row">
                            <span class="progress-label">Current Occupancy</span>
                            <span class="progress-value">{{ $occupancyRate }}%</span>
                        </div>
                        <div class="progress progress-modern" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $occupancyRate }}">
                            <div class="progress-bar" @style(['width' => $occupancyRate . '%'])></div>
                        </div>
                        <div class="occupancy-foot small text-muted mt-2">
                            {{ $occupiedRooms }} occupied of {{ $totalRooms }} total rooms
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="analytics-card h-100">
                <div class="analytics-card-header">
                    <h5 class="mb-0">Booking Trends (Last 7 Days)</h5>
                </div>
                <div class="analytics-card-body chart-body">
                    <canvas id="bookingChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="analytics-card mb-4">
        <div class="analytics-card-header">
            <h5 class="mb-0">Top Performing Properties</h5>
        </div>
        <div class="analytics-card-body">
            @if($topProperties->isNotEmpty())
                <div class="top-properties-grid">
                    @foreach($topProperties as $index => $property)
                        @php
                            $occupancy = $property->total_rooms > 0
                                ? round(($property->occupied_rooms / $property->total_rooms) * 100, 1)
                                : 0;
                        @endphp
                        <article class="top-property-item">
                            <div class="top-property-rank">#{{ $index + 1 }}</div>
                            <div class="top-property-main">
                                <div class="top-property-title">{{ $property->name }}</div>
                                <div class="top-property-address">{{ $property->address }}</div>
                                <div class="top-property-metrics">
                                    <span class="metric-chip"><i class="bi bi-journal-check"></i>{{ $property->booking_count }} bookings</span>
                                    <span class="metric-chip"><i class="bi bi-bar-chart"></i>{{ $occupancy }}% occupancy</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-bar-chart fs-1 mb-2"></i>
                    <div class="empty-title">No Booking Data Yet</div>
                    <div class="empty-copy">Top property insights will appear after approved bookings are recorded.</div>
                </div>
            @endif
        </div>
    </div>

    <div class="analytics-card">
        <div class="analytics-card-header">
            <h5 class="mb-0">Property Performance Details</h5>
        </div>
        <div class="analytics-card-body p-0">
            @if($properties->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 analytics-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Total</th>
                                <th>Occupied</th>
                                <th>Available</th>
                                <th>Maintenance</th>
                                <th>Occupancy</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($properties as $property)
                                @php
                                    $rate = $property->total_rooms > 0 ? round(($property->occupied_rooms / $property->total_rooms) * 100, 1) : 0;
                                    $rateClass = $rate > 70 ? 'status-approved' : ($rate > 30 ? 'status-pending' : 'status-default');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $property->name }}</div>
                                        <div class="small text-muted text-truncate" style="max-width: 340px;">{{ $property->address }}</div>
                                    </td>
                                    <td>{{ $property->total_rooms }}</td>
                                    <td>{{ $property->occupied_rooms }}</td>
                                    <td>{{ $property->available_rooms }}</td>
                                    <td>{{ $property->maintenance_rooms }}</td>
                                    <td><span class="status-pill {{ $rateClass }}">{{ $rate }}%</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-house fs-1 mb-2"></i>
                    <div class="empty-title">No Properties Found</div>
                    <div class="empty-copy">Property analytics will appear once your property records are available.</div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .analytics-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .updated-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        padding: .16rem .5rem;
        font-size: .72rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .analytics-summary {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: .75rem;
    }
    .analytics-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
    }
    .analytics-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .analytics-summary-value {
        font-size: .95rem;
        font-weight: 700;
        color: #0f172a;
    }
    .analytics-card {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: #fff;
        overflow: hidden;
    }
    .analytics-card-header {
        border-bottom: 1px solid rgba(2,8,20,.08);
        padding: .85rem 1rem;
        background: rgba(248,250,252,.78);
    }
    .analytics-card-body {
        padding: 1rem;
    }
    .chart-body {
        height: 260px;
    }
    .progress-label-row {
        display: flex;
        justify-content: space-between;
        gap: .6rem;
        align-items: center;
        margin-bottom: .25rem;
    }
    .progress-label {
        font-size: .73rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 700;
    }
    .progress-value {
        font-size: .78rem;
        color: #14532d;
        font-weight: 700;
    }
    .progress-modern {
        height: .6rem;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .progress-modern .progress-bar {
        background: linear-gradient(90deg, #14532d, #16a34a);
    }
    .top-properties-grid {
        display: grid;
        gap: .65rem;
    }
    .top-property-item {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: .7rem;
        align-items: start;
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .85rem;
        padding: .75rem .8rem;
        background: #fcfefe;
    }
    .top-property-rank {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .72rem;
        font-weight: 800;
        color: #14532d;
        border: 1px solid rgba(20,83,45,.2);
        background: rgba(167,243,208,.3);
    }
    .top-property-title {
        font-weight: 700;
        color: #14532d;
        line-height: 1.2;
    }
    .top-property-address {
        font-size: .77rem;
        color: #64748b;
        margin-top: .1rem;
    }
    .top-property-metrics {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-top: .45rem;
    }
    .metric-chip {
        display: inline-flex;
        align-items: center;
        gap: .32rem;
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 999px;
        background: #f8fafc;
        color: #0f172a;
        padding: .16rem .52rem;
        font-size: .72rem;
        font-weight: 600;
    }
    .analytics-table thead th {
        font-size: .73rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: rgba(2,8,20,.58);
        font-weight: 800;
        background: #fafcfe;
        border-bottom-color: rgba(2,8,20,.09);
    }
    .analytics-table tbody td {
        font-size: .83rem;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .22rem .62rem;
        font-size: .76rem;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .status-pending {
        color: #7c2d12;
        background: #ffedd5;
        border-color: #fdba74;
    }
    .status-approved {
        color: #14532d;
        background: #dcfce7;
        border-color: #86efac;
    }
    .status-default {
        color: #1f2937;
        background: #f3f4f6;
        border-color: #d1d5db;
    }
    .empty-state {
        text-align: center;
        color: #64748b;
        padding: 2.4rem 1rem;
    }
    .empty-state i {
        color: rgba(2,8,20,.2);
    }
    .empty-title {
        color: #0f172a;
        font-weight: 700;
        margin-bottom: .35rem;
    }
    .empty-copy {
        max-width: 520px;
        margin: 0 auto;
        font-size: .9rem;
    }

    @media (max-width: 1199.98px) {
        .analytics-summary {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .analytics-shell {
            padding: .95rem;
        }
        .analytics-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .chart-body {
            height: 230px;
        }
    }

    @media (max-width: 575.98px) {
        .analytics-summary {
            grid-template-columns: 1fr;
        }
        .top-property-item {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('bookingChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const bookingData = @json($weeklyBookings);
    const labels = bookingData.map(item => new Date(item.date).toLocaleDateString());
    const values = bookingData.map(item => item.count);

    const gradient = ctx.createLinearGradient(0, 0, 0, 220);
    gradient.addColorStop(0, 'rgba(20, 83, 45, 0.28)');
    gradient.addColorStop(1, 'rgba(20, 83, 45, 0.02)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Bookings',
                data: values,
                borderColor: '#14532d',
                backgroundColor: gradient,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#166534',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Bookings: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b',
                        maxRotation: 0,
                        autoSkip: true
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, .22)'
                    },
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        color: '#64748b'
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection