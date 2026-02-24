@extends('layouts.landlord')

@section('title', 'Review Documents')

@section('content')
  <div class="glass-card rounded-4 p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h4 mb-0">Review Documents: {{ $onboarding->booking->student->full_name }}</h1>
      <a href="{{ route('landlord.onboarding.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <!-- Student Information -->
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0">Student Information</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <strong>Name:</strong> {{ $onboarding->booking->student->full_name }}<br>
          <strong>Email:</strong> {{ $onboarding->booking->student->email }}<br>
          <strong>Student ID:</strong> {{ $onboarding->booking->student->student_id ?? 'N/A' }}
        </div>
        <div class="col-md-6">
          <strong>Property:</strong> {{ $onboarding->booking->room->property->name }}<br>
          <strong>Room:</strong> {{ $onboarding->booking->room->room_number }}<br>
          <strong>Lease Period:</strong> {{ $onboarding->booking->check_in->format('M d, Y') }} - {{ $onboarding->booking->check_out->format('M d, Y') }}
        </div>
      </div>
    </div>
  </div>

  <!-- Onboarding Status -->
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0">Onboarding Status</h5>
    </div>
    <div class="card-body">
      <div class="mb-3">
        <strong>Current Status:</strong>
        <span class="badge @if($onboarding->status === 'completed') bg-success @elseif($onboarding->status === 'pending') bg-warning @else bg-info @endif ms-2">
          {{ ucfirst(str_replace('_', ' ', $onboarding->status)) }}
        </span>
      </div>

      @if($onboarding->contract_signed)
        <div class="mb-3">
          <strong>Contract Signed:</strong> {{ $onboarding->contract_signed_at ? $onboarding->contract_signed_at->format('M d, Y H:i') : 'N/A' }}
        </div>
      @endif

      @if($onboarding->deposit_paid)
        <div class="mb-3">
          <strong>Deposit Paid:</strong> ₱{{ number_format($onboarding->deposit_amount, 2) }} on {{ $onboarding->deposit_paid_at ? $onboarding->deposit_paid_at->format('M d, Y') : 'N/A' }}
        </div>
      @endif

      @if($onboarding->digital_id)
        <div class="mb-3">
          <strong>Digital Tenant ID:</strong> {{ $onboarding->digital_id }}
        </div>
      @endif
    </div>
  </div>

  <!-- Uploaded Documents -->
  @if($onboarding->uploaded_documents && count($onboarding->uploaded_documents) > 0)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Uploaded Documents</h5>
      </div>
      <div class="card-body">
        <div class="row">
          @foreach($onboarding->uploaded_documents as $index => $doc)
            <div class="col-md-6 mb-3">
              <div class="card h-100">
                <div class="card-body text-center">
                  @php
                    $fileName = basename($doc);
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $fileSize = Storage::disk('public')->size($doc);
                    $fileSizeFormatted = $fileSize < 1024 ? $fileSize . ' B' : ($fileSize < 1048576 ? round($fileSize / 1024, 1) . ' KB' : round($fileSize / 1048576, 1) . ' MB');
                  @endphp

                  @if(in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
                    <i class="fas fa-image fa-2x text-success mb-2"></i>
                  @elseif($fileExtension === 'pdf')
                    <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                  @else
                    <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                  @endif

                  <h6 class="card-title mb-1">{{ $fileName }}</h6>
                  <p class="text-muted small mb-2">{{ $fileSizeFormatted }}</p>

                  <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}" target="_blank" class="btn btn-sm btn-brand">
                      <i class="fas fa-eye me-1"></i>View
                    </a>
                    <a href="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}?download=1" class="btn btn-sm btn-outline-secondary">
                      <i class="fas fa-download me-1"></i>Download
                    </a>
                  </div>

                  @if(in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
                    <div class="mt-3">
                      <img src="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}" alt="Document Preview" class="img-thumbnail" style="max-height: 150px; max-width: 100%;">
                    </div>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Document Review Actions -->
    @if($onboarding->status === 'pending')
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Document Review</h5>
        </div>
        <div class="card-body">
          <p>Please review the uploaded documents and either approve them to continue the onboarding process or reject them for the student to re-upload.</p>

          <form method="POST" action="{{ route('landlord.onboarding.approve_documents', $onboarding) }}">
            @csrf
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="action" id="approve" value="approve" checked>
                <label class="form-check-label" for="approve">
                  <strong>Approve Documents</strong> - Allow the student to proceed to contract signing
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="action" id="reject" value="reject">
                <label class="form-check-label" for="reject">
                  <strong>Reject Documents</strong> - Require the student to upload documents again
                </label>
              </div>
            </div>
            <button type="submit" class="btn btn-brand">Submit Review</button>
          </form>
        </div>
      </div>
    @endif
  @else
    <div class="card">
      <div class="card-body text-center">
        <h5>No Documents Uploaded</h5>
        <p class="text-muted">The student has not uploaded any documents yet.</p>
      </div>
    </div>
  @endif

  </div>
@endsection