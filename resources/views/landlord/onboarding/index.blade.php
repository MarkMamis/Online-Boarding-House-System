@extends('layouts.landlord')

@section('title', 'Onboarding')

@section('content')
  <div class="onboarding-shell">
    @php
      $pendingCount = $onboardings->where('status', 'pending')->count();
      $docsUploadedCount = $onboardings->where('status', 'documents_uploaded')->count();
      $inProgressCount = $onboardings->whereIn('status', ['contract_signed', 'deposit_paid'])->count();
      $completedCount = $onboardings->where('status', 'completed')->count();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <div class="text-uppercase small text-muted fw-semibold">Tenant Pipeline</div>
        <h1 class="h3 mb-1">Onboarding Management</h1>
        <div class="text-muted small">Track onboarding progress from document submission to completed move-in setup.</div>
      </div>
      <a href="{{ route('landlord.tenants.index') }}" class="btn btn-outline-secondary rounded-pill px-3">View Tenants</a>
    </div>

    <div class="onboarding-summary mb-4">
      <div class="onboarding-summary-item">
        <div class="onboarding-summary-label">Pending</div>
        <div class="onboarding-summary-value text-warning-emphasis">{{ $pendingCount }}</div>
      </div>
      <div class="onboarding-summary-item">
        <div class="onboarding-summary-label">Documents Uploaded</div>
        <div class="onboarding-summary-value text-info-emphasis">{{ $docsUploadedCount }}</div>
      </div>
      <div class="onboarding-summary-item">
        <div class="onboarding-summary-label">In Progress</div>
        <div class="onboarding-summary-value text-primary-emphasis">{{ $inProgressCount }}</div>
      </div>
      <div class="onboarding-summary-item">
        <div class="onboarding-summary-label">Completed</div>
        <div class="onboarding-summary-value text-success-emphasis">{{ $completedCount }}</div>
      </div>
    </div>

    @if($onboardings->isEmpty())
      <div class="onboarding-list-card">
        <div class="empty-state">
          <i class="bi bi-clipboard-check fs-1 mb-2"></i>
          <div class="empty-title">No Onboarding Processes</div>
          <div class="empty-copy">No tenants are currently going through onboarding.</div>
        </div>
      </div>
    @else
      <div class="onboarding-list-card">
        @foreach($onboardings as $onboarding)
          @php
            $status = strtolower((string) $onboarding->status);
            $progress = match($status) {
              'pending' => 25,
              'documents_uploaded' => 50,
              'contract_signed' => 75,
              'deposit_paid' => 90,
              'completed' => 100,
              default => 0,
            };

            $statusClass = match($status) {
              'pending' => 'status-pending',
              'documents_uploaded' => 'status-info',
              'contract_signed' => 'status-primary',
              'deposit_paid' => 'status-primary',
              'completed' => 'status-approved',
              default => 'status-default',
            };

            $statusIcon = match($status) {
              'pending' => 'bi-hourglass-split',
              'documents_uploaded' => 'bi-file-earmark-check',
              'contract_signed' => 'bi-pencil-square',
              'deposit_paid' => 'bi-cash-stack',
              'completed' => 'bi-check-circle',
              default => 'bi-info-circle',
            };

            $statusLabel = ucfirst(str_replace('_', ' ', $status));
            $statusNote = match($status) {
              'pending' => 'Waiting for student upload',
              'documents_uploaded' => 'Ready for document review',
              'contract_signed' => 'Contract has been signed',
              'deposit_paid' => 'Payment submitted, awaiting your approval',
              'completed' => 'Onboarding is complete',
              default => 'Progress status unavailable',
            };

            if ($status === 'deposit_paid') {
              $statusLabel = 'Payment Under Review';
            }

            $hasDocuments = is_array($onboarding->uploaded_documents ?? null) && count($onboarding->uploaded_documents) > 0;
            $needsPaymentReview = $status === 'deposit_paid' && empty($onboarding->deposit_paid_at);
            $isActionPrimary = (in_array($status, ['pending', 'documents_uploaded'], true) && $hasDocuments) || $needsPaymentReview;
            $actionLabel = $needsPaymentReview ? 'Review Payment' : ($isActionPrimary ? 'Review Documents' : 'View Details');
            $actionClass = $isActionPrimary ? 'btn-brand' : 'btn-outline-secondary';
          @endphp

          <article class="onboarding-item">
            <div class="onboarding-main">
              <div class="onboarding-title-row">
                <div class="d-flex align-items-center gap-2 min-w-0">
                  <div class="student-avatar">
                    {{ strtoupper(substr($onboarding->booking->student->full_name ?? 'S', 0, 1)) }}
                  </div>
                  <div class="min-w-0">
                    <div class="student-name text-truncate">{{ $onboarding->booking->student->full_name }}</div>
                    <div class="student-email text-truncate">{{ $onboarding->booking->student->email }}</div>
                  </div>
                </div>
                <span class="updated-chip"><i class="bi bi-clock-history"></i>{{ $onboarding->updated_at->diffForHumans() }}</span>
              </div>

              <div class="onboarding-meta-row">
                <span class="meta-chip"><i class="bi bi-building"></i>{{ $onboarding->booking->room->property->name }}</span>
                <span class="meta-chip"><i class="bi bi-door-open"></i>{{ $onboarding->booking->room->room_number }}</span>
                <span class="meta-chip"><i class="bi bi-calendar-range"></i>{{ optional($onboarding->booking->check_in)->format('M d, Y') }} - {{ optional($onboarding->booking->check_out)->format('M d, Y') }}</span>
              </div>

              <div class="progress-wrap">
                <div class="progress-label-row">
                  <span class="progress-label">Progress</span>
                  <span class="progress-value">{{ $progress }}%</span>
                </div>
                <div class="progress progress-modern" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}">
                  <div class="progress-bar" style="width: {{ $progress }}%;"></div>
                </div>
              </div>
            </div>

            <div class="onboarding-side">
              <div class="status-panel">
                <span class="status-pill {{ $statusClass }}"><i class="bi {{ $statusIcon }}"></i>{{ $statusLabel }}</span>
                <div class="status-note">{{ $statusNote }}</div>
              </div>

              <div class="onboarding-actions">
                <a href="{{ route('landlord.onboarding.review', $onboarding) }}" class="btn btn-sm {{ $actionClass }} rounded-pill action-btn">{{ $actionLabel }}</a>
              </div>
            </div>
          </article>
        @endforeach
      </div>
    @endif
  </div>
@endsection

@push('styles')
<style>
  .onboarding-shell {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1.25rem;
    box-shadow: 0 10px 26px rgba(2,8,20,.06);
    padding: 1.25rem;
  }
  .onboarding-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
  }
  .onboarding-summary-item {
    border: 1px solid rgba(20,83,45,.16);
    background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
    border-radius: .9rem;
    padding: .7rem .8rem;
  }
  .onboarding-summary-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(2,8,20,.55);
    font-weight: 700;
    margin-bottom: .2rem;
  }
  .onboarding-summary-value {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
  }
  .onboarding-list-card {
    border: 1px solid rgba(2,8,20,.09);
    border-radius: 1rem;
    background: #fff;
    overflow: hidden;
  }
  .onboarding-item {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: .9rem;
    align-items: start;
    padding: 1rem;
    border-bottom: 1px solid rgba(2,8,20,.08);
  }
  .onboarding-item:last-child {
    border-bottom: none;
  }
  .onboarding-title-row {
    display: flex;
    justify-content: space-between;
    gap: .6rem;
    align-items: start;
    margin-bottom: .45rem;
  }
  .student-avatar {
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
  .student-name {
    font-weight: 700;
    color: #14532d;
    line-height: 1.2;
  }
  .student-email {
    font-size: .78rem;
    color: #64748b;
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
  .onboarding-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
    align-items: center;
    margin-bottom: .7rem;
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
  .progress-wrap {
    max-width: 430px;
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
    height: .52rem;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
  }
  .progress-modern .progress-bar {
    background: linear-gradient(90deg, #14532d, #16a34a);
  }
  .onboarding-side {
    display: grid;
    gap: .45rem;
    min-width: 230px;
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
  .status-info {
    color: #1e3a8a;
    background: #dbeafe;
    border-color: #93c5fd;
  }
  .status-primary {
    color: #0f766e;
    background: #ccfbf1;
    border-color: #5eead4;
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
  .onboarding-actions {
    border: 1px dashed rgba(100,116,139,.35);
    border-radius: .7rem;
    padding: .35rem .5rem;
    background: rgba(248,250,252,.9);
  }
  .onboarding-actions .btn {
    min-width: 148px;
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
    .onboarding-shell {
      padding: .95rem;
    }
    .onboarding-summary {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .onboarding-item {
      grid-template-columns: 1fr;
    }
    .onboarding-side {
      justify-items: start;
      min-width: 0;
    }
    .status-panel {
      text-align: left;
    }
  }

  @media (max-width: 575.98px) {
    .onboarding-summary {
      grid-template-columns: 1fr;
    }
    .onboarding-title-row {
      flex-direction: column;
    }
    .updated-chip {
      align-self: flex-start;
    }
    .onboarding-actions,
    .onboarding-actions .btn {
      width: 100%;
    }
  }
</style>
@endpush
