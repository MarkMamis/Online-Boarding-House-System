@extends('layouts.student')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Profile</h5>
                        <div>
                            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                            <a href="{{ route('student.profile.show') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i>Back to Profile
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h6 class="mb-3"><i class="fas fa-user me-2"></i>Basic Information</h6>

                                <div class="mb-3">
                                    <label class="form-label">Profile Photo</label>
                                    <div class="d-flex align-items-center gap-3">
                                        @if(!empty($user->profile_image_path))
                                            <img id="profile_image_preview" src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile photo" class="rounded-circle border" style="width: 72px; height: 72px; object-fit: cover;">
                                        @else
                                            <img id="profile_image_preview" src="" alt="Profile photo" class="rounded-circle border d-none" style="width: 72px; height: 72px; object-fit: cover;">
                                            <div id="profile_image_placeholder" class="rounded-circle border bg-light d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <input id="profile_image_input" type="file" class="form-control @error('profile_image') is-invalid @enderror" name="profile_image" accept="image/*">
                                            @error('profile_image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">JPG, PNG, WEBP, GIF (max 2MB)</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                           id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required>
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control @error('contact_number') is-invalid @enderror"
                                           id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}">
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="birth_date" class="form-label">Birth Date</label>
                                    <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                                           id="birth_date" name="birth_date" value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}">
                                    @error('birth_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror"
                                              id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Student Information -->
                            <div class="col-md-6">
                                <h6 class="mb-3"><i class="fas fa-graduation-cap me-2"></i>Student Information</h6>

                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Student ID</label>
                                    <input type="text" class="form-control @error('student_id') is-invalid @enderror"
                                           id="student_id" name="student_id" value="{{ old('student_id', $user->student_id) }}">
                                    @error('student_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="course" class="form-label">Course/Program</label>
                                    <input type="text" class="form-control @error('course') is-invalid @enderror"
                                           id="course" name="course" value="{{ old('course', $user->course) }}">
                                    @error('course')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="year_level" class="form-label">Year Level</label>
                                    <select class="form-control @error('year_level') is-invalid @enderror" id="year_level" name="year_level">
                                        <option value="">Select Year Level</option>
                                        <option value="1st Year" {{ old('year_level', $user->year_level) == '1st Year' ? 'selected' : '' }}>1st Year</option>
                                        <option value="2nd Year" {{ old('year_level', $user->year_level) == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                                        <option value="3rd Year" {{ old('year_level', $user->year_level) == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                                        <option value="4th Year" {{ old('year_level', $user->year_level) == '4th Year' ? 'selected' : '' }}>4th Year</option>
                                        <option value="5th Year" {{ old('year_level', $user->year_level) == '5th Year' ? 'selected' : '' }}>5th Year</option>
                                    </select>
                                    @error('year_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Emergency Contact -->
                            <div class="col-md-6">
                                <h6 class="mb-3"><i class="fas fa-phone me-2"></i>Emergency Contact</h6>

                                <div class="mb-3">
                                    <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                                    <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                           id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}">
                                    @error('emergency_contact_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="emergency_contact_number" class="form-label">Emergency Contact Number</label>
                                    <input type="tel" class="form-control @error('emergency_contact_number') is-invalid @enderror"
                                           id="emergency_contact_number" name="emergency_contact_number" value="{{ old('emergency_contact_number', $user->emergency_contact_number) }}">
                                    @error('emergency_contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                                    <input type="text" class="form-control @error('emergency_contact_relationship') is-invalid @enderror"
                                           id="emergency_contact_relationship" name="emergency_contact_relationship"
                                           value="{{ old('emergency_contact_relationship', $user->emergency_contact_relationship) }}"
                                           placeholder="e.g., Parent, Sibling, Friend">
                                    @error('emergency_contact_relationship')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Guardian Information -->
                            <div class="col-md-6">
                                <h6 class="mb-3"><i class="fas fa-user-friends me-2"></i>Guardian Information</h6>

                                <div class="mb-3">
                                    <label for="guardian_name" class="form-label">Guardian Name</label>
                                    <input type="text" class="form-control @error('guardian_name') is-invalid @enderror"
                                           id="guardian_name" name="guardian_name" value="{{ old('guardian_name', $user->guardian_name) }}">
                                    @error('guardian_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="guardian_contact" class="form-label">Guardian Contact</label>
                                    <input type="tel" class="form-control @error('guardian_contact') is-invalid @enderror"
                                           id="guardian_contact" name="guardian_contact" value="{{ old('guardian_contact', $user->guardian_contact) }}">
                                    @error('guardian_contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Medical Information -->
                        <div class="mb-4">
                            <h6 class="mb-3"><i class="fas fa-heartbeat me-2"></i>Medical Information</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="blood_type" class="form-label">Blood Type</label>
                                        <select class="form-control @error('blood_type') is-invalid @enderror" id="blood_type" name="blood_type">
                                            <option value="">Select Blood Type</option>
                                            <option value="A+" {{ old('blood_type', $user->blood_type) == 'A+' ? 'selected' : '' }}>A+</option>
                                            <option value="A-" {{ old('blood_type', $user->blood_type) == 'A-' ? 'selected' : '' }}>A-</option>
                                            <option value="B+" {{ old('blood_type', $user->blood_type) == 'B+' ? 'selected' : '' }}>B+</option>
                                            <option value="B-" {{ old('blood_type', $user->blood_type) == 'B-' ? 'selected' : '' }}>B-</option>
                                            <option value="AB+" {{ old('blood_type', $user->blood_type) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                            <option value="AB-" {{ old('blood_type', $user->blood_type) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                            <option value="O+" {{ old('blood_type', $user->blood_type) == 'O+' ? 'selected' : '' }}>O+</option>
                                            <option value="O-" {{ old('blood_type', $user->blood_type) == 'O-' ? 'selected' : '' }}>O-</option>
                                        </select>
                                        @error('blood_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="allergies" class="form-label">Allergies</label>
                                        <input type="text" class="form-control @error('allergies') is-invalid @enderror"
                                               id="allergies" name="allergies" value="{{ old('allergies', $user->allergies) }}"
                                               placeholder="e.g., Peanuts, Shellfish, Penicillin">
                                        @error('allergies')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="medications" class="form-label">Current Medications</label>
                                <textarea class="form-control @error('medications') is-invalid @enderror"
                                          id="medications" name="medications" rows="2"
                                          placeholder="Please list any medications you are currently taking">{{ old('medications', $user->medications) }}</textarea>
                                @error('medications')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="medical_conditions" class="form-label">Medical Conditions</label>
                                <textarea class="form-control @error('medical_conditions') is-invalid @enderror"
                                          id="medical_conditions" name="medical_conditions" rows="3"
                                          placeholder="Please list any medical conditions or special health requirements">{{ old('medical_conditions', $user->medical_conditions) }}</textarea>
                                @error('medical_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('student.profile.show') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('profile_image_input');
        const preview = document.getElementById('profile_image_preview');
        const placeholder = document.getElementById('profile_image_placeholder');
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) return;
            if (!file.type || !file.type.startsWith('image/')) return;

            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        });
    })();
</script>
@endpush