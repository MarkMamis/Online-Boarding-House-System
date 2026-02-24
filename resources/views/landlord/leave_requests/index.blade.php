@extends('layouts.landlord')

@section('title', 'Leave Requests')

@section('content')
    <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <div class="small text-muted">Operations</div>
                <h1 class="h4 mb-0">Leave Requests</h1>
            </div>
        </div>

        <div class="table-responsive rounded-4 bg-white shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Property</th>
                        <th>Room</th>
                        <th>Leave Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($leaveRequests ?? collect()) as $lr)
                        <tr>
                            <td class="fw-semibold">{{ $lr->student?->full_name ?? 'Student' }}</td>
                            <td>{{ $lr->booking?->room?->property?->name ?? 'Property' }}</td>
                            <td>Room {{ $lr->booking?->room?->room_number ?? '—' }}</td>
                            <td>{{ optional($lr->leave_date)->format('M d, Y') }}</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit((string)($lr->reason ?? ''), 70) }}</td>
                            <td><span class="badge text-bg-light">{{ $lr->status ?? '—' }}</span></td>
                            <td class="text-end">
                                @if(($lr->status ?? '') === 'pending')
                                    <form method="POST" action="{{ route('landlord.leave_requests.approve', $lr->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-brand rounded-pill">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('landlord.leave_requests.reject', $lr->id) }}" class="d-inline ms-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">Reject</button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                        @if(!empty($lr->landlord_response))
                            <tr>
                                <td colspan="7" class="small">
                                    <span class="text-muted">Response:</span> {{ $lr->landlord_response }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="p-3 text-muted">No leave requests yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
