<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Onboarding Process</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="{{ route('student.dashboard') }}">Student</a>
    <div class="ms-auto">
      <a href="{{ route('student.onboarding.index') }}" class="btn btn-sm btn-outline-light me-2">My Onboarding</a>
      <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-light me-2">Messages</a>
      <form class="d-inline" method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Logout</button></form>
    </div>
  </div>
</nav>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Onboarding: {{ $onboarding->booking->room->property->name }} - Room {{ $onboarding->booking->room->room_number }}</h1>
    <a href="{{ route('student.onboarding.index') }}" class="btn btn-outline-secondary">Back</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <!-- Progress Steps -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row text-center">
        <div class="col-md-2">
          <div class="step-circle @if(in_array($onboarding->status, ['documents_uploaded', 'contract_signed', 'deposit_paid', 'completed'])) bg-success @else bg-secondary @endif">
            1
          </div>
          <small>Documents</small>
        </div>
        <div class="col-md-1 d-flex align-items-center">
          <hr class="flex-grow-1">
        </div>
        <div class="col-md-2">
          <div class="step-circle @if(in_array($onboarding->status, ['contract_signed', 'deposit_paid', 'completed'])) bg-success @elseif($onboarding->status === 'documents_uploaded') bg-primary @else bg-secondary @endif">
            2
          </div>
          <small>Contract</small>
        </div>
        <div class="col-md-1 d-flex align-items-center">
          <hr class="flex-grow-1">
        </div>
        <div class="col-md-2">
          <div class="step-circle @if(in_array($onboarding->status, ['deposit_paid', 'completed'])) bg-success @elseif($onboarding->status === 'contract_signed') bg-primary @else bg-secondary @endif">
            3
          </div>
          <small>Deposit</small>
        </div>
        <div class="col-md-1 d-flex align-items-center">
          <hr class="flex-grow-1">
        </div>
        <div class="col-md-2">
          <div class="step-circle @if($onboarding->status === 'completed') bg-success @elseif($onboarding->status === 'deposit_paid') bg-primary @else bg-secondary @endif">
            4
          </div>
          <small>Complete</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Step 1: Document Upload -->
  @if($onboarding->status === 'pending')
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Step 1: Upload Required Documents</h5>
      </div>
      <div class="card-body">
        <p>Please upload the following required documents:</p>
        <ul>
          @foreach($onboarding->required_documents ?? [] as $doc)
            <li>{{ ucfirst(str_replace('_', ' ', $doc)) }}</li>
          @endforeach
        </ul>

        <form method="POST" action="{{ route('student.onboarding.upload_documents', $onboarding) }}" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label for="documents" class="form-label">Select Documents (PDF, JPG, PNG - Max 5MB each)</label>
            <input type="file" class="form-control" id="documents" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="form-text">You can select multiple files at once</div>
          </div>
          <button type="submit" class="btn btn-primary">Upload Documents</button>
        </form>
      </div>
    </div>
  @endif

  <!-- Step 2: Contract Signing -->
  @if($onboarding->status === 'documents_uploaded')
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Step 2: Review and Sign Contract</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Contract Content:</label>
          <div class="border p-3 bg-light" style="white-space: pre-line;">
            {{ $onboarding->contract_content }}
          </div>
        </div>

        <form method="POST" action="{{ route('student.onboarding.sign_contract', $onboarding) }}">
          @csrf
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="agree" required>
            <label class="form-check-label" for="agree">
              I agree to the terms and conditions outlined in this contract
            </label>
          </div>
          <button type="submit" class="btn btn-primary">Sign Contract</button>
        </form>
      </div>
    </div>
  @endif

  <!-- Step 3: Deposit Payment -->
  @if($onboarding->status === 'contract_signed')
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Step 3: Pay Security Deposit</h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <strong>Deposit Amount: ₱{{ number_format($onboarding->deposit_amount, 2) }}</strong><br>
          This is a 50% deposit of your monthly rent (₱{{ number_format($onboarding->booking->room->price, 2) }}).
        </div>

        <p>In a real application, you would be redirected to a payment gateway here.</p>

        <form method="POST" action="{{ route('student.onboarding.pay_deposit', $onboarding) }}">
          @csrf
          <button type="submit" class="btn btn-success">Simulate Deposit Payment</button>
        </form>
      </div>
    </div>
  @endif

  <!-- Completion Status -->
  @if($onboarding->status === 'completed')
    <div class="card mb-4">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0">✓ Onboarding Completed!</h5>
      </div>
      <div class="card-body">
        <div class="alert alert-success">
          <h6>Congratulations!</h6>
          <p>Your tenant onboarding process has been completed successfully. You can now move into your room.</p>
        </div>

        @if($onboarding->digital_id)
          <div class="mb-3">
            <strong>Your Digital Tenant ID:</strong>
            <div class="bg-light p-2 rounded font-monospace">{{ $onboarding->digital_id }}</div>
            <small class="text-muted">Keep this ID safe - you'll need it for check-in and other services.</small>
          </div>
        @endif

        <div class="row">
          <div class="col-md-6">
            <strong>Property:</strong> {{ $onboarding->booking->room->property->name }}<br>
            <strong>Room:</strong> {{ $onboarding->booking->room->room_number }}<br>
            <strong>Lease Period:</strong> {{ $onboarding->booking->check_in->format('M d, Y') }} - {{ $onboarding->booking->check_out->format('M d, Y') }}
          </div>
          <div class="col-md-6">
            <strong>Deposit Paid:</strong> ₱{{ number_format($onboarding->deposit_amount, 2) }}<br>
            <strong>Contract Signed:</strong> {{ $onboarding->contract_signed_at ? $onboarding->contract_signed_at->format('M d, Y') : 'N/A' }}
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- Uploaded Documents (if any) -->
  @if($onboarding->uploaded_documents && count($onboarding->uploaded_documents) > 0)
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Uploaded Documents</h5>
        @if($onboarding->status === 'pending')
          <span class="badge bg-warning">
            <i class="fas fa-clock me-1"></i>Awaiting Review
          </span>
        @elseif($onboarding->status === 'documents_uploaded')
          <span class="badge bg-success">
            <i class="fas fa-check me-1"></i>Approved
          </span>
        @endif
      </div>
      <div class="card-body">
        @if($onboarding->status === 'pending')
          <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle me-2"></i>
            Your documents have been uploaded and are currently under review by the landlord.
            You will be notified once the review is complete.
          </div>
        @endif

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
                    <a href="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}" target="_blank" class="btn btn-sm btn-primary">
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
  @endif
</main>

<style>
.step-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  margin: 0 auto 5px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>