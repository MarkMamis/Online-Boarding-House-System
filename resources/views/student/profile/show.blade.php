@extends('layouts.student')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        @if(!empty($user->profile_image_path))
                            <img src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile photo" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;">
                        @else
                            <div class="rounded-circle border bg-light d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="fas fa-user text-muted"></i>
                            </div>
                        @endif
                        <h5 class="mb-0">My Profile</h5>
                    </div>
                    <div>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                        <a href="{{ route('student.profile.edit') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                        <a href="{{ route('student.profile.change-password') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-key me-2"></i>Change Password
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card border mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Full Name:</strong></div>
                                        <div class="col-sm-8">{{ $user->full_name ?? 'Not provided' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Email:</strong></div>
                                        <div class="col-sm-8">{{ $user->email }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Phone:</strong></div>
                                        <div class="col-sm-8">{{ $user->contact_number ?? 'Not provided' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Birth Date:</strong></div>
                                        <div class="col-sm-8">{{ $user->birth_date ? $user->birth_date->format('M d, Y') : 'Not provided' }}</div>
                                    </div>
                                    <div class="row mb-0">
                                        <div class="col-sm-4"><strong>Address:</strong></div>
                                        <div class="col-sm-8">{{ $user->address ?? 'Not provided' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Student Information -->
                        <div class="col-md-6">
                            <div class="card border mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Student Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Student ID:</strong></div>
                                        <div class="col-sm-8">{{ $user->student_id ?? 'Not provided' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Course:</strong></div>
                                        <div class="col-sm-8">{{ $user->course ?? 'Not provided' }}</div>
                                    </div>
                                    <div class="row mb-0">
                                        <div class="col-sm-4"><strong>Year Level:</strong></div>
                                        <div class="col-sm-8">{{ $user->year_level ?? 'Not provided' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Emergency Contact -->
                        <div class="col-md-6">
                            <div class="card border mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-phone me-2"></i>Emergency Contact</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Name:</strong></div>
                                        <div class="col-sm-8">{{ $user->emergency_contact_name ?? 'Not provided' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Phone:</strong></div>
                                        <div class="col-sm-8">{{ $user->emergency_contact_number ?? 'Not provided' }}</div>
                                    </div>
                                    <div class="row mb-0">
                                        <div class="col-sm-4"><strong>Relationship:</strong></div>
                                        <div class="col-sm-8">{{ $user->emergency_contact_relationship ?? 'Not provided' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Guardian Information -->
                        <div class="col-md-6">
                            <div class="card border mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user-friends me-2"></i>Guardian Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Name:</strong></div>
                                        <div class="col-sm-8">{{ $user->guardian_name ?? 'Not provided' }}</div>
                                    </div>
                                    <div class="row mb-0">
                                        <div class="col-sm-4"><strong>Contact:</strong></div>
                                        <div class="col-sm-8">{{ $user->guardian_contact ?? 'Not provided' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Conditions -->
                    @if($user->medical_conditions || $user->blood_type || $user->allergies || $user->medications)
                    <div class="card border">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Medical Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($user->blood_type)
                                <div class="col-md-6 mb-3">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Blood Type:</strong></div>
                                        <div class="col-sm-8">{{ $user->blood_type }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($user->allergies)
                                <div class="col-md-6 mb-3">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Allergies:</strong></div>
                                        <div class="col-sm-8">{{ $user->allergies }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($user->medications)
                                <div class="col-md-6 mb-3">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Medications:</strong></div>
                                        <div class="col-sm-8">{{ $user->medications }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($user->medical_conditions)
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-sm-2"><strong>Conditions:</strong></div>
                                        <div class="col-sm-10">{{ $user->medical_conditions }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection