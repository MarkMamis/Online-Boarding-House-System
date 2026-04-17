@extends('layouts.landlord')

@section('content')
<div class="tenants-shell">
    @php
        $totalTenants = $tenants->count();
        $paidTenants = $tenants->filter(fn ($t) => $t->derivedPaymentStatus() === 'paid')->count();
        $pendingPayments = $tenants->filter(fn ($t) => $t->derivedPaymentStatus() === 'pending')->count();
        $overduePayments = $tenants->filter(fn ($t) => $t->derivedPaymentStatus() === 'overdue')->count();
        $activeLeases = $tenants->filter(fn ($t) => !empty($t->check_out) && $t->check_out->isFuture())->count();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Tenant Management</div>
            <h1 class="h3 mb-1">Current Tenants</h1>
            <div class="text-muted small">Track active residents, lease dates, and payment follow-ups.</div>
        </div>
        <a href="{{ route('landlord.bookings.index') }}" class="btn btn-outline-secondary rounded-pill px-3">View Booking Requests</a>
    </div>

    <div class="tenant-summary mb-4">
        <div class="tenant-summary-item">
            <div class="tenant-summary-label">Total Tenants</div>
            <div class="tenant-summary-value">{{ $totalTenants }}</div>
        </div>
        <div class="tenant-summary-item">
            <div class="tenant-summary-label">Paid</div>
            <div class="tenant-summary-value text-success-emphasis">{{ $paidTenants }}</div>
        </div>
        <div class="tenant-summary-item">
            <div class="tenant-summary-label">Pending Payments</div>
            <div class="tenant-summary-value text-warning-emphasis">{{ $pendingPayments }}</div>
        </div>
        <div class="tenant-summary-item">
            <div class="tenant-summary-label">Overdue</div>
            <div class="tenant-summary-value text-danger-emphasis">{{ $overduePayments }}</div>
        </div>
        <div class="tenant-summary-item">
            <div class="tenant-summary-label">Active Leases</div>
            <div class="tenant-summary-value text-primary-emphasis">{{ $activeLeases }}</div>
        </div>
    </div>

    <div class="tenant-toolbar mb-3">
        <div class="toolbar-search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="tenantSearchInput" class="form-control" placeholder="Search tenant, room, property, or contact...">
        </div>
        <div class="toolbar-filter-wrap">
            <i class="bi bi-funnel"></i>
            <select id="tenantPaymentFilter" class="form-select">
                <option value="all">All payments</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="overdue">Overdue</option>
            </select>
        </div>
    </div>

    <div class="tenant-list-card">
        @forelse($tenants as $tenant)
            @php
                $student = $tenant->student;
                $room = $tenant->room;
                $property = $room?->property;
                $paymentStatus = strtolower((string) $tenant->derivedPaymentStatus());
                $dueDate = optional($tenant->resolvePaymentDueDate());
                $paymentLabel = match ($paymentStatus) {
                    'paid' => 'Paid',
                    'pending' => 'Pending',
                    'overdue' => 'Overdue',
                    default => 'Pending',
                };

                $statusClass = match ($paymentStatus) {
                    'paid' => 'status-approved',
                    'pending' => 'status-pending',
                    'overdue' => 'status-overdue',
                    default => 'status-pending',
                };
                $statusIcon = match ($paymentStatus) {
                    'paid' => 'bi-check-circle',
                    'pending' => 'bi-hourglass-split',
                    'overdue' => 'bi-exclamation-octagon',
                    default => 'bi-hourglass-split',
                };
                $statusNote = match ($paymentStatus) {
                    'paid' => 'Payment is up to date',
                    'pending' => 'Awaiting payment confirmation',
                    'overdue' => 'Payment due date has passed' . ($dueDate ? ' since ' . $dueDate->format('M d, Y') : ''),
                    default => 'Awaiting payment confirmation',
                };

                $searchBlob = strtolower(trim(implode(' ', [
                    $student->full_name ?? '',
                    $student->year_level ?? '',
                    $room->room_number ?? '',
                    $property->name ?? '',
                    $student->emergency_contact_name ?? '',
                    $student->emergency_contact_number ?? '',
                    $student->parent_contact_name ?? '',
                    $student->parent_contact_address ?? '',
                ])));
            @endphp

            <article class="tenant-item" data-search="{{ $searchBlob }}" data-payment="{{ $paymentStatus }}">
                <div class="tenant-main">
                    <div class="tenant-header-row">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="tenant-avatar">
                                {{ strtoupper(substr($student->full_name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="tenant-name text-truncate">{{ $student->full_name ?? 'Unknown Tenant' }}</div>
                                <div class="tenant-email text-truncate">
                                    Year Level: {{ $student->year_level ?: 'Not provided' }}
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="tenant-meta-row">
                        <span class="meta-chip"><i class="bi bi-door-open"></i>{{ $room->room_number ?? 'N/A' }}</span>
                        <span class="meta-chip"><i class="bi bi-building"></i>{{ $property->name ?? 'Property' }}</span>
                        <span class="meta-chip"><i class="bi bi-calendar-range"></i>{{ optional($tenant->check_in)->format('M d, Y') }} - {{ optional($tenant->check_out)->format('M d, Y') }}</span>
                    </div>

                    <div class="tenant-contact-grid">
                        <div class="tenant-contact-item">
                            <span class="tenant-contact-label">Emergency Contact</span>
                            <span class="tenant-contact-value">
                                {{ $student->emergency_contact_name ?: 'Not provided' }}
                            </span>
                        </div>
                        <div class="tenant-contact-item">
                            <span class="tenant-contact-label">Emergency Contact Number</span>
                            <span class="tenant-contact-value">
                                {{ $student->emergency_contact_number ?: 'Not provided' }}
                            </span>
                        </div>
                        <div class="tenant-contact-item">
                            <span class="tenant-contact-label">Parent/Guardian Name</span>
                            <span class="tenant-contact-value">
                                {{ $student->parent_contact_name ?: 'Not provided' }}
                            </span>
                        </div>
                        <div class="tenant-contact-item">
                            <span class="tenant-contact-label">Parent Address</span>
                            <span class="tenant-contact-value">
                                {{ $student->parent_contact_address ?: 'Not provided' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="tenant-side">
                    <div class="status-panel">
                        <span class="status-pill {{ $statusClass }}"><i class="bi {{ $statusIcon }}"></i>{{ $paymentLabel }}</span>
                        <div class="status-note">{{ $statusNote }}</div>
                        @if($paymentStatus === 'overdue')
                            <div class="status-danger-note">Follow up immediately to avoid extended arrears.</div>
                        @endif
                    </div>

                    <div class="tenant-actions">
                    <a href="{{ route('landlord.messages.index') }}?to={{ $student->id }}" class="btn btn-sm btn-brand rounded-pill action-btn" title="Send message">
                        <i class="bi bi-chat-dots"></i><span>Message</span>
                    </a>
                    <a href="{{ route('landlord.properties.show', $room->property_id) }}" class="btn btn-sm btn-outline-secondary rounded-pill action-btn" title="View property">
                        <i class="bi bi-house-door"></i><span>Property</span>
                    </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <i class="bi bi-people fs-1 mb-2"></i>
                <div class="empty-title">No Current Tenants</div>
                <div class="empty-copy">You do not have approved stays yet. Once bookings are approved, tenants will appear here.</div>
                <a href="{{ route('landlord.bookings.index') }}" class="btn btn-brand rounded-pill px-3 mt-2">Check Booking Requests</a>
            </div>
        @endforelse

        @if($tenants->isNotEmpty())
            <div id="tenantFilterEmpty" class="empty-state d-none">
                <i class="bi bi-search fs-1 mb-2"></i>
                <div class="empty-title">No matching tenants</div>
                <div class="empty-copy">Try a different search term or payment filter.</div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .tenants-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .tenant-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .75rem;
    }
    .tenant-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
    }
    .tenant-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .tenant-summary-value {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .tenant-list-card {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: #fff;
        overflow: hidden;
    }
    .tenant-toolbar {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 210px;
        gap: .7rem;
    }
    .toolbar-search-wrap,
    .toolbar-filter-wrap {
        position: relative;
    }
    .toolbar-search-wrap i,
    .toolbar-filter-wrap i {
        position: absolute;
        left: .7rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        pointer-events: none;
    }
    .toolbar-search-wrap .form-control,
    .toolbar-filter-wrap .form-select {
        border-radius: .8rem;
        border-color: rgba(2,8,20,.14);
        padding-left: 2rem;
        background: #fff;
    }
    .toolbar-filter-wrap .form-select {
        font-size: .9rem;
    }
    .tenant-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .9rem;
        align-items: start;
        padding: 1rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
        transition: background .2s ease, box-shadow .2s ease;
    }
    .tenant-item:hover {
        background: linear-gradient(180deg, rgba(248,250,252,.6), rgba(255,255,255,.9));
    }
    .tenant-item:last-child {
        border-bottom: none;
    }
    .tenant-header-row {
        display: flex;
        justify-content: space-between;
        gap: .55rem;
        align-items: start;
        margin-bottom: .45rem;
    }
    .tenant-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #14532d;
        background: rgba(167,243,208,.35);
        border: 1px solid rgba(20,83,45,.2);
        flex: 0 0 auto;
    }
    .tenant-name {
        font-weight: 700;
        color: #14532d;
        line-height: 1.2;
    }
    .tenant-email {
        font-size: .78rem;
        color: #64748b;
    }
    .tenant-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        align-items: center;
        margin-bottom: .6rem;
    }
    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 999px;
        background: #f8fafc;
        color: #0f172a;
        padding: .18rem .55rem;
        font-size: .78rem;
        font-weight: 600;
    }
    .tenant-contact-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .5rem;
    }
    .tenant-contact-item {
        display: flex;
        flex-direction: column;
        gap: .1rem;
    }
    .tenant-contact-label {
        font-size: .69rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        font-weight: 700;
    }
    .tenant-contact-value {
        font-size: .8rem;
        color: #0f172a;
        font-weight: 600;
    }
    .tenant-side {
        display: grid;
        gap: .45rem;
        min-width: 240px;
        justify-items: end;
    }
    .status-panel {
        text-align: right;
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
    .status-note {
        margin-top: .24rem;
        font-size: .74rem;
        color: rgba(2,8,20,.56);
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
    .status-overdue {
        color: #7f1d1d;
        background: #fee2e2;
        border-color: #fca5a5;
    }
    .status-danger-note {
        margin-top: .28rem;
        color: #b91c1c;
        font-size: .73rem;
        font-weight: 700;
    }
    .tenant-actions {
        display: inline-flex;
        gap: .45rem;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
        border: 1px dashed rgba(100,116,139,.35);
        border-radius: .7rem;
        padding: .35rem .5rem;
        background: rgba(248,250,252,.9);
    }
    .tenant-actions .btn {
        min-width: 98px;
    }
    .tenant-action-title {
        display: none;
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
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

    @media (max-width: 991.98px) {
        .tenants-shell {
            padding: .95rem;
        }
        .tenant-toolbar {
            grid-template-columns: 1fr;
        }
        .tenant-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .tenant-item {
            grid-template-columns: 1fr;
        }
        .tenant-side {
            justify-items: start;
            min-width: 0;
        }
        .status-panel {
            text-align: left;
        }
        .tenant-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .tenant-summary {
            grid-template-columns: 1fr;
        }
        .tenant-contact-grid {
            grid-template-columns: 1fr;
        }
        .tenant-actions {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%;
        }
        .tenant-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const searchInput = document.getElementById('tenantSearchInput');
        const paymentFilter = document.getElementById('tenantPaymentFilter');
        const items = Array.from(document.querySelectorAll('.tenant-item'));
        const emptyState = document.getElementById('tenantFilterEmpty');

        if (!items.length || !searchInput || !paymentFilter) return;

        const applyFilters = () => {
            const query = (searchInput.value || '').trim().toLowerCase();
            const payment = (paymentFilter.value || 'all').toLowerCase();
            let visibleCount = 0;

            items.forEach((item) => {
                const haystack = (item.dataset.search || '').toLowerCase();
                const itemPayment = (item.dataset.payment || '').toLowerCase();
                const matchQuery = !query || haystack.includes(query);
                const matchPayment = payment === 'all' || itemPayment === payment;
                const visible = matchQuery && matchPayment;

                item.classList.toggle('d-none', !visible);
                if (visible) visibleCount += 1;
            });

            if (emptyState) {
                emptyState.classList.toggle('d-none', visibleCount > 0);
            }
        };

        searchInput.addEventListener('input', applyFilters);
        paymentFilter.addEventListener('change', applyFilters);
    })();
</script>
@endpush
