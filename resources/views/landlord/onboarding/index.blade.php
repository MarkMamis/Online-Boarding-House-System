@extends('layouts.landlord')

@section('title', 'Onboarding')

@section('content')
  <div class="glass-card rounded-4 p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0">Tenant Onboarding Management</h1>
    </div>

  @if($onboardings->isEmpty())
    <div class="card shadow-sm">
      <div class="card-body text-center py-5">
        <h5 class="card-title">No Onboarding Processes</h5>
        <p class="card-text text-muted">No tenants are currently going through the onboarding process.</p>
      </div>
    </div>
  @else
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Student</th>
              <th>Property</th>
              <th>Room</th>
              <th>Status</th>
              <th>Progress</th>
              <th>Last Updated</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($onboardings as $onboarding)
              <tr>
                <td>
                  {{ $onboarding->booking->student->full_name }}<br>
                  <small class="text-muted">{{ $onboarding->booking->student->email }}</small>
                </td>
                <td>{{ $onboarding->booking->room->property->name }}</td>
                <td>{{ $onboarding->booking->room->room_number }}</td>
                <td>
                  <span class="badge @if($onboarding->status === 'completed') bg-success @elseif($onboarding->status === 'pending') bg-warning @else bg-info @endif">
                    {{ ucfirst(str_replace('_', ' ', $onboarding->status)) }}
                  </span>
                </td>
                <td>
                  <div class="progress" style="width: 100px; height: 8px;">
                    @php
                      $progress = match($onboarding->status) {
                        'pending' => 25,
                        'documents_uploaded' => 50,
                        'contract_signed' => 75,
                        'deposit_paid' => 90,
                        'completed' => 100,
                        default => 0
                      };
                    @endphp
                    <div class="progress-bar" role="progressbar" @style(['width' => $progress . '%'])></div>
                  </div>
                  <small class="text-muted">{{ $progress }}%</small>
                </td>
                <td>{{ $onboarding->updated_at->diffForHumans() }}</td>
                <td class="text-end">
                  @if($onboarding->status === 'pending')
                    <a href="{{ route('landlord.onboarding.review', $onboarding) }}" class="btn btn-sm btn-brand">Review Documents</a>
                  @else
                    <a href="{{ route('landlord.onboarding.review', $onboarding) }}" class="btn btn-sm btn-outline-brand">View Details</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
  </div>
@endsection