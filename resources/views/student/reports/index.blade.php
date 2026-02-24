@extends('layouts.student')

@section('title', 'My Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">My Reports</h4>
                    <div>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                        <a href="{{ route('student.reports.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Submit New Report
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Title</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Admin Response</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td>
                                            <strong>{{ $report->title }}</strong>
                                        </td>
                                        <td>
                                            @if($report->priority === 'high')
                                                <span class="badge bg-danger">High</span>
                                            @elseif($report->priority === 'medium')
                                                <span class="badge bg-warning">Medium</span>
                                            @else
                                                <span class="badge bg-success">Low</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($report->status === 'pending')
                                                <span class="badge bg-secondary">Pending</span>
                                            @elseif($report->status === 'in_progress')
                                                <span class="badge bg-primary">In Progress</span>
                                            @else
                                                <span class="badge bg-success">Resolved</span>
                                            @endif
                                        </td>
                                        <td>{{ $report->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @if($report->admin_response)
                                                @if(!$report->response_read)
                                                    <span class="badge bg-info">New Response</span>
                                                @else
                                                    <span class="text-success">✓ Read</span>
                                                @endif
                                            @else
                                                <span class="text-muted">No response yet</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-report-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#reportModal"
                                                    data-report-id="{{ $report->id }}"
                                                    data-title="{{ $report->title }}"
                                                    data-description="{{ $report->description }}"
                                                    data-status="{{ $report->status }}"
                                                    data-priority="{{ $report->priority }}"
                                                    data-admin-response="{{ $report->admin_response }}"
                                                    data-created-at="{{ $report->created_at->format('F d, Y \a\t g:i A') }}"
                                                    data-resolved-at="{{ $report->resolved_at ? $report->resolved_at->format('F d, Y \a\t g:i A') : '' }}"
                                                    data-response-read="{{ $report->response_read }}">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>You haven't submitted any reports yet.</p>
                                                <a href="{{ route('student.reports.create') }}" class="btn btn-primary">
                                                    Submit Your First Report
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($reports->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $reports->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Detail Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Title:</strong></div>
                    <div class="col-sm-9" id="modal-title"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Priority:</strong></div>
                    <div class="col-sm-9" id="modal-priority"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Status:</strong></div>
                    <div class="col-sm-9" id="modal-status"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Submitted:</strong></div>
                    <div class="col-sm-9" id="modal-created-at"></div>
                </div>
                <div class="row mb-3" id="resolved-row" style="display: none;">
                    <div class="col-sm-3"><strong>Resolved:</strong></div>
                    <div class="col-sm-9" id="modal-resolved-at"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Description:</strong></div>
                    <div class="col-sm-9">
                        <div class="bg-light p-3 rounded" id="modal-description"></div>
                    </div>
                </div>
                <div class="row" id="admin-response-row" style="display: none;">
                    <div class="col-sm-3"><strong>Admin Response:</strong></div>
                    <div class="col-sm-9">
                        <div class="bg-success bg-opacity-10 border border-success rounded p-3" id="modal-admin-response"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="mark-read-btn" style="display: none;">Mark as Read</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportModal = document.getElementById('reportModal');
    const markReadBtn = document.getElementById('mark-read-btn');

    reportModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const reportId = button.getAttribute('data-report-id');
        const title = button.getAttribute('data-title');
        const description = button.getAttribute('data-description');
        const status = button.getAttribute('data-status');
        const priority = button.getAttribute('data-priority');
        const adminResponse = button.getAttribute('data-admin-response');
        const createdAt = button.getAttribute('data-created-at');
        const resolvedAt = button.getAttribute('data-resolved-at');
        const responseRead = button.getAttribute('data-response-read') === '1';

        // Set modal content
        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-description').innerHTML = description.replace(/\n/g, '<br>');

        // Priority badge
        let priorityBadge = '';
        if (priority === 'high') {
            priorityBadge = '<span class="badge bg-danger">High</span>';
        } else if (priority === 'medium') {
            priorityBadge = '<span class="badge bg-warning">Medium</span>';
        } else {
            priorityBadge = '<span class="badge bg-success">Low</span>';
        }
        document.getElementById('modal-priority').innerHTML = priorityBadge;

        // Status badge
        let statusBadge = '';
        if (status === 'pending') {
            statusBadge = '<span class="badge bg-secondary">Pending</span>';
        } else if (status === 'in_progress') {
            statusBadge = '<span class="badge bg-primary">In Progress</span>';
        } else {
            statusBadge = '<span class="badge bg-success">Resolved</span>';
        }
        document.getElementById('modal-status').innerHTML = statusBadge;

        document.getElementById('modal-created-at').textContent = createdAt;

        // Show resolved date if available
        const resolvedRow = document.getElementById('resolved-row');
        if (resolvedAt) {
            document.getElementById('modal-resolved-at').textContent = resolvedAt;
            resolvedRow.style.display = 'flex';
        } else {
            resolvedRow.style.display = 'none';
        }

        // Show admin response if available
        const responseRow = document.getElementById('admin-response-row');
        if (adminResponse) {
            document.getElementById('modal-admin-response').innerHTML = adminResponse.replace(/\n/g, '<br>');
            responseRow.style.display = 'flex';

            // Show mark as read button if not already read
            if (!responseRead) {
                markReadBtn.style.display = 'inline-block';
                markReadBtn.setAttribute('data-report-id', reportId);
            } else {
                markReadBtn.style.display = 'none';
            }
        } else {
            responseRow.style.display = 'none';
            markReadBtn.style.display = 'none';
        }
    });

    // Mark response as read
    markReadBtn.addEventListener('click', function() {
        const reportId = this.getAttribute('data-report-id');

        fetch(`/student/reports/${reportId}/mark-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                markReadBtn.style.display = 'none';
                // Reload the page to update the badge count
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});
</script>
@endsection