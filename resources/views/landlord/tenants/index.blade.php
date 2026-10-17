@extends('layouts.landlord')

@section('title', 'Current Tenants')

@section('content')
<div class="landlord-page-shell">
    @php
        $totalTenants = $tenants->count();
        $paidTenants = $tenants->filter(fn ($t) => $t->derivedPaymentStatus() === 'paid')->count();
        $pendingPayments = $tenants->filter(fn ($t) => $t->derivedPaymentStatus() === 'pending')->count();
        $overduePayments = $tenants->filter(fn ($t) => $t->derivedPaymentStatus() === 'overdue')->count();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="landlord-header-eyebrow">Tenant Management</div>
            <h1 class="landlord-header-title mb-1">Current Tenants</h1>
            <div class="landlord-header-subtitle">Track active residents, stay dates, and payment follow-ups.</div>
        </div>
        @if($totalTenants > 0)
            <div class="d-flex align-items-center gap-2">
                <span class="landlord-summary-strip">
                    <span class="landlord-summary-dot"></span>
                    <span>{{ $totalTenants }} {{ Str::plural('Resident', $totalTenants) }}</span>
                    @if($pendingPayments > 0)
                        <span class="text-muted">·</span>
                        <span class="text-warning-emphasis fw-bold">{{ $pendingPayments }} pending payment</span>
                    @endif
                    @if($overduePayments > 0)
                        <span class="text-muted">·</span>
                        <span class="text-danger fw-bold">{{ $overduePayments }} overdue</span>
                    @endif
                </span>
            </div>
        @endif
    </div>

    @if($tenants->isNotEmpty())
        <div class="landlord-filter-bar">
            <div class="landlord-filter-search">
                <i class="bi bi-search"></i>
                <input type="text" id="tenantSearchInput" class="form-control" placeholder="Search tenant, room, property, or contact...">
            </div>
            <div class="landlord-filter-select">
                <select id="tenantResidencyFilter" class="form-select">
                    <option value="all" selected>All residents</option>
                    <option value="active">Active residents</option>
                    <option value="movein">Move-in in progress</option>
                    <option value="past">Past stays</option>
                </select>
            </div>
            <div class="landlord-filter-select">
                <select id="tenantPaymentFilter" class="form-select">
                    <option value="all">All payments</option>
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
        </div>
    @endif

    <div class="landlord-card">
        @forelse($tenants as $tenant)
            @php
                $student = $tenant->student;
                $room = $tenant->room;
                $property = $room?->property;
                $paymentStatus = strtolower((string) $tenant->derivedPaymentStatus());
                $dueDate = optional($tenant->resolvePaymentDueDate());
                $onboarding = $tenant->tenantOnboarding;
                $isOnboarding = $onboarding && (string) $onboarding->status !== 'completed';
                $isPastStay = $tenant->check_out && $tenant->check_out->isPast();
                $residencyStatus = $isOnboarding ? 'movein' : ($isPastStay ? 'past' : 'active');

                $paymentLabel = match ($paymentStatus) {
                    'paid' => 'Paid',
                    'pending' => 'Pending',
                    'overdue' => 'Overdue',
                    default => 'Pending',
                };

                $statusBadgeClass = match ($paymentStatus) {
                    'paid' => 'bg-success-subtle text-success border border-success-subtle',
                    'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                    'overdue' => 'bg-danger-subtle text-danger border border-danger-subtle',
                    default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
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

            <article class="tenant-item" data-search="{{ $searchBlob }}" data-payment="{{ $paymentStatus }}" data-residency="{{ $residencyStatus }}">
                <div class="tenant-main">
                    <div class="tenant-header-row">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="tenant-avatar">
                                {{ strtoupper(substr($student->full_name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="tenant-name text-truncate">{{ $student->full_name ?? 'Unknown Tenant' }}</span>
                                    @if($isOnboarding)
                                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:0.75rem;">
                                            <i class="bi bi-clock me-1"></i>Move-in In Progress
                                        </span>
                                    @elseif($isPastStay)
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:0.75rem;">
                                            Past Stay
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle" style="font-size:0.75rem;">
                                            <i class="bi bi-person-check-fill me-1"></i>Active Resident
                                        </span>
                                    @endif
                                </div>
                                <div class="tenant-email text-truncate">
                                    Year Level: {{ $student->year_level ?: 'Not provided' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tenant-meta-row">
                        <span class="meta-chip"><i class="bi bi-door-open"></i>Room {{ $room->room_number ?? 'N/A' }}</span>
                        <span class="meta-chip"><i class="bi bi-building"></i>{{ $property->name ?? 'Property' }}</span>
                        <span class="meta-chip"><i class="bi bi-calendar-range"></i>{{ optional($tenant->check_in)->format('M d, Y') }} - {{ optional($tenant->check_out)->format('M d, Y') }}</span>
                    </div>

                    <div class="tenant-contact-grid">
                        <div class="tenant-contact-item">
                            <span class="tenant-contact-label">Emergency Contact</span>
                            <span class="tenant-contact-value">
                                {{ $student->emergency_contact_name ?: 'Not provided' }}
                                @if($student->emergency_contact_number)
                                    <span class="text-muted fw-normal">({{ $student->emergency_contact_number }})</span>
                                @endif
                            </span>
                        </div>
                        <div class="tenant-contact-item">
                            <span class="tenant-contact-label">Parent / Guardian</span>
                            <span class="tenant-contact-value">
                                {{ $student->parent_contact_name ?: 'Not provided' }}
                                @if($student->parent_contact_address)
                                    <span class="text-muted fw-normal">· {{ $student->parent_contact_address }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="tenant-side">
                    <div class="status-panel">
                        <span class="badge rounded-pill {{ $statusBadgeClass }} px-2 py-1"><i class="bi {{ $statusIcon }} me-1"></i>{{ $paymentLabel }}</span>
                        <div class="status-note">{{ $statusNote }}</div>
                        @if($paymentStatus === 'overdue')
                            <div class="status-danger-note">Follow up immediately to avoid extended arrears.</div>
                        @endif
                    </div>

                    <div class="tenant-actions">
                        @if($isOnboarding)
                            <a href="{{ route('landlord.onboarding.review', $onboarding->id) }}" class="btn btn-sm btn-outline-warning rounded-pill action-btn" title="View Move-In Step">
                                <i class="bi bi-clipboard-check"></i><span>Move-In</span>
                            </a>
                        @endif
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
            <div class="landlord-empty-state border-0">
                <div class="landlord-empty-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="landlord-empty-title">No Current Tenants</div>
                <div class="landlord-empty-text">You do not have active resident stays yet. Once incoming student boarding requests are approved and onboarded, tenants will appear here.</div>
            </div>
        @endforelse

        @if($tenants->isNotEmpty())
            <div id="tenantFilterEmpty" class="landlord-empty-state border-0 d-none">
                <div class="landlord-empty-icon">
                    <i class="bi bi-search"></i>
                </div>
                <div class="landlord-empty-title">No matching tenants</div>
                <div class="landlord-empty-text">Try adjusting your search query or residency/payment filter.</div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .tenant-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: start;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
        transition: background .2s ease;
    }
    .tenant-item:hover {
        background: #fbfcfd;
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
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #14532d;
        background: rgba(167,243,208,.35);
        border: 1px solid rgba(20,83,45,.2);
        flex: 0 0 auto;
        font-size: .95rem;
    }
    .tenant-name {
        font-weight: 700;
        color: #0f172a;
        font-size: .95rem;
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
        border: 1px solid rgba(2,8,20,.1);
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        padding: .15rem .55rem;
        font-size: .76rem;
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
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        font-weight: 700;
    }
    .tenant-contact-value {
        font-size: .8rem;
        color: #1e293b;
        font-weight: 600;
    }
    .tenant-side {
        display: grid;
        gap: .6rem;
        min-width: 210px;
        justify-items: end;
    }
    .status-panel {
        text-align: right;
    }
    .status-note {
        margin-top: .25rem;
        font-size: .73rem;
        color: #64748b;
    }
    .status-danger-note {
        margin-top: .25rem;
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
    }
    .tenant-actions .btn {
        min-width: 82px;
        font-size: .82rem;
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
    }

    @media (max-width: 991.98px) {
        .tenant-item {
            grid-template-columns: 1fr;
        }
        .tenant-side {
            justify-items: start;
            min-width: 0;
            width: 100%;
            border-top: 1px dashed rgba(2,8,20,.08);
            padding-top: .75rem;
        }
        .status-panel {
            text-align: left;
        }
        .tenant-actions {
            justify-content: flex-start;
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .tenant-contact-grid {
            grid-template-columns: 1fr;
        }
        .tenant-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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
        const residencyFilter = document.getElementById('tenantResidencyFilter');
        const items = Array.from(document.querySelectorAll('.tenant-item'));
        const emptyState = document.getElementById('tenantFilterEmpty');

        if (!items.length || !searchInput || !paymentFilter) return;

        const applyFilters = () => {
            const query = (searchInput.value || '').trim().toLowerCase();
            const payment = (paymentFilter.value || 'all').toLowerCase();
            const residency = (residencyFilter?.value || 'all').toLowerCase();
            let visibleCount = 0;

            items.forEach((item) => {
                const haystack = (item.dataset.search || '').toLowerCase();
                const itemPayment = (item.dataset.payment || '').toLowerCase();
                const itemResidency = (item.dataset.residency || '').toLowerCase();
                const matchQuery = !query || haystack.includes(query);
                const matchPayment = payment === 'all' || itemPayment === payment;
                const matchResidency = residency === 'all' || itemResidency === residency;
                const visible = matchQuery && matchPayment && matchResidency;

                item.classList.toggle('d-none', !visible);
                if (visible) visibleCount += 1;
            });

            if (emptyState) {
                emptyState.classList.toggle('d-none', visibleCount > 0);
            }
        };

        searchInput.addEventListener('input', applyFilters);
        paymentFilter.addEventListener('change', applyFilters);
        if (residencyFilter) {
            residencyFilter.addEventListener('change', applyFilters);
        }
    })();
</script>
@endpush
