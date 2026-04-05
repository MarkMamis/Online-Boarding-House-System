@extends('layouts.admin')

@section('title', 'Edit Student - ' . $user->full_name)

@section('content')
<style>
    .student-edit-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }
    .muted { color: rgba(2, 8, 20, .58); }
    .section-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }
    .section-header {
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: #fff;
        padding: .85rem 1rem;
    }
    .form-input {
        border: 1px solid rgba(2, 8, 20, .12);
        border-radius: .6rem;
        padding: .65rem .85rem;
        font-size: .95rem;
    }
    .form-input:focus {
        border-color: #166534;
        box-shadow: 0 0 0 3px rgba(22, 101, 52, .1);
    }
    .form-label {
        font-size: .88rem;
        font-weight: 600;
        color: rgba(2, 8, 20, .78);
        margin-bottom: .35rem;
    }
    .form-group { margin-bottom: 1rem; }
    .btn-save {
        background: #166534;
        border: 1px solid #166534;
        color: #fff;
        padding: .65rem 1.5rem;
        border-radius: .6rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .18s ease;
    }
    .btn-save:hover {
        background: #12522a;
        border-color: #12522a;
    }
    .btn-cancel {
        background: #f1f5f9;
        border: 1px solid rgba(2, 8, 20, .12);
        color: rgba(2, 8, 20, .78);
        padding: .65rem 1.5rem;
        border-radius: .6rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all .18s ease;
        display: inline-block;
    }
    .btn-cancel:hover {
        background: #e2e8f0;
    }
    .error-alert {
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: .6rem;
        padding: .85rem 1rem;
        color: #991b1b;
        margin-bottom: 1.25rem;
    }
    .success-alert {
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        border-radius: .6rem;
        padding: .85rem 1rem;
        color: #065f46;
        margin-bottom: 1.25rem;
    }
    .form-error {
        color: #dc2626;
        font-size: .8rem;
        margin-top: .25rem;
    }
    .radio-group {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 1rem;
    }
    .radio-item {
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .radio-item input[type="radio"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #166534;
    }
    .radio-item label {
        cursor: pointer;
        margin: 0;
        font-weight: 500;
        color: rgba(2, 8, 20, .78);
    }
    .other-gender-field {
        display: none;
        margin-top: .75rem;
    }
    .other-gender-field.show {
        display: block;
    }
    @media (max-width: 767.98px) {
        .student-edit-shell { padding: .95rem; }
    }
</style>

<div class="student-edit-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small muted fw-semibold">Edit Student Account</div>
            <h1 class="h4 mb-1">{{ $user->full_name }}</h1>
            <div class="muted small">Update student information, emergency contacts, and medical details.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.students.show', $user) }}" class="btn-cancel">
                Cancel
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="error-alert">
        <strong>Validation Errors:</strong>
        <ul class="mb-0 mt-2" style="padding-left: 1.5rem;">
            @foreach ($errors->all() as $error)
            <li style="margin-bottom: .25rem;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.users.students.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Personal Information -->
        <div class="section-card mb-4">
            <div class="section-header fw-semibold"><i class="bi bi-person-vcard me-1"></i> Personal Information</div>
            <div class="p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-input w-100 @error('full_name') is-invalid @enderror" 
                                   name="full_name" value="{{ old('full_name', $user->full_name) }}" required>
                            @error('full_name')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-input w-100 @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Gender</label>
                            <div class="radio-group @error('gender') is-invalid @enderror">
                                <div class="radio-item">
                                    <input type="radio" id="gender_male" name="gender" value="Male" 
                                           {{ old('gender', $user->gender) === 'Male' ? 'checked' : '' }}>
                                    <label for="gender_male">Male</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="gender_female" name="gender" value="Female" 
                                           {{ old('gender', $user->gender) === 'Female' ? 'checked' : '' }}>
                                    <label for="gender_female">Female</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="gender_other" name="gender" value="Other" 
                                           {{ old('gender', $user->gender) === 'Other' ? 'checked' : '' }}>
                                    <label for="gender_other">Other</label>
                                </div>
                            </div>
                            <div class="other-gender-field {{ old('gender', $user->gender) === 'Other' ? 'show' : '' }}" id="otherGenderField">
                                <input type="text" class="form-input w-100" 
                                       name="gender_other" placeholder="Please specify your gender"
                                       value="{{ old('gender_other', '') }}">
                            </div>
                            @error('gender')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-input w-100 @error('contact_number') is-invalid @enderror" 
                                   name="contact_number" value="{{ old('contact_number', $user->contact_number) }}">
                            @error('contact_number')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Student ID</label>
                            <input type="text" class="form-input w-100 @error('student_id') is-invalid @enderror" 
                                   name="student_id" value="{{ old('student_id', $user->student_id) }}">
                            @error('student_id')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Course</label>
                            <input type="text" class="form-input w-100 @error('course') is-invalid @enderror" 
                                   name="course" value="{{ old('course', $user->course) }}">
                            @error('course')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Year Level</label>
                            <select class="form-input w-100 @error('year_level') is-invalid @enderror" 
                                    name="year_level">
                                <option value="">Select Year Level</option>
                                <option value="1st Year" {{ old('year_level', $user->year_level) === '1st Year' ? 'selected' : '' }}>1st Year</option>
                                <option value="2nd Year" {{ old('year_level', $user->year_level) === '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                                <option value="3rd Year" {{ old('year_level', $user->year_level) === '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                                <option value="4th Year" {{ old('year_level', $user->year_level) === '4th Year' ? 'selected' : '' }}>4th Year</option>
                            </select>
                            @error('year_level')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Birth Date</label>
                            <input type="date" class="form-input w-100 @error('birth_date') is-invalid @enderror" 
                                   name="birth_date" value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}">
                            @error('birth_date')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-input w-100 @error('address') is-invalid @enderror" 
                                   name="address" value="{{ old('address', $user->address) }}">
                            @error('address')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Contact Information -->
        <div class="section-card mb-4">
            <div class="section-header fw-semibold"><i class="bi bi-phone me-1"></i> Emergency Contact Information</div>
            <div class="p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Contact Name</label>
                            <input type="text" class="form-input w-100 @error('emergency_contact_name') is-invalid @enderror" 
                                   name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}">
                            @error('emergency_contact_name')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-input w-100 @error('emergency_contact_number') is-invalid @enderror" 
                                   name="emergency_contact_number" value="{{ old('emergency_contact_number', $user->emergency_contact_number) }}">
                            @error('emergency_contact_number')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Relationship</label>
                            <input type="text" class="form-input w-100 @error('emergency_contact_relationship') is-invalid @enderror" 
                                   name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $user->emergency_contact_relationship) }}">
                            @error('emergency_contact_relationship')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Guardian Name</label>
                            <input type="text" class="form-input w-100 @error('guardian_name') is-invalid @enderror" 
                                   name="guardian_name" value="{{ old('guardian_name', $user->guardian_name) }}">
                            @error('guardian_name')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Guardian Contact</label>
                            <input type="text" class="form-input w-100 @error('guardian_contact') is-invalid @enderror" 
                                   name="guardian_contact" value="{{ old('guardian_contact', $user->guardian_contact) }}">
                            @error('guardian_contact')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Parent Contact Name</label>
                            <input type="text" class="form-input w-100 @error('parent_contact_name') is-invalid @enderror"
                                   name="parent_contact_name" value="{{ old('parent_contact_name', $user->parent_contact_name) }}">
                            @error('parent_contact_name')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Parent Contact Number</label>
                            <input type="text" class="form-input w-100 @error('parent_contact_number') is-invalid @enderror" 
                                   name="parent_contact_number" value="{{ old('parent_contact_number', $user->parent_contact_number) }}">
                            @error('parent_contact_number')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Parent/Guardian Address</label>
                            <textarea class="form-input w-100 @error('parent_contact_address') is-invalid @enderror"
                                      name="parent_contact_address" rows="2" placeholder="Emergency home address">{{ old('parent_contact_address', $user->parent_contact_address) }}</textarea>
                            @error('parent_contact_address')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Information -->
        <div class="section-card mb-4">
            <div class="section-header fw-semibold"><i class="bi bi-hospital me-1"></i> Medical Information</div>
            <div class="p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Blood Type</label>
                            <input type="text" class="form-input w-100 @error('blood_type') is-invalid @enderror" 
                                   name="blood_type" value="{{ old('blood_type', $user->blood_type) }}" placeholder="e.g., O+">
                            @error('blood_type')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6"></div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Allergies</label>
                            <textarea class="form-input w-100 @error('allergies') is-invalid @enderror" 
                                      name="allergies" rows="2" placeholder="List any known allergies">{{ old('allergies', $user->allergies) }}</textarea>
                            @error('allergies')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Medical Conditions</label>
                            <textarea class="form-input w-100 @error('medical_conditions') is-invalid @enderror" 
                                      name="medical_conditions" rows="2" placeholder="List any medical conditions">{{ old('medical_conditions', $user->medical_conditions) }}</textarea>
                            @error('medical_conditions')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Medications</label>
                            <textarea class="form-input w-100 @error('medications') is-invalid @enderror" 
                                      name="medications" rows="2" placeholder="List any medications">{{ old('medications', $user->medications) }}</textarea>
                            @error('medications')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex flex-wrap gap-2 justify-content-between">
            <div>
                <button type="submit" class="btn-save">
                    <i class="bi bi-check-circle me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.users.students.show', $user) }}" class="btn-cancel ms-2">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<script>
    // Handle gender "Other" field visibility
    const genderRadios = document.querySelectorAll('input[name="gender"]');
    const otherGenderField = document.getElementById('otherGenderField');

    function toggleOtherGenderField() {
        const isOtherSelected = document.getElementById('gender_other').checked;
        if (isOtherSelected) {
            otherGenderField.classList.add('show');
        } else {
            otherGenderField.classList.remove('show');
        }
    }

    // Add event listeners to all gender radio buttons
    genderRadios.forEach(radio => {
        radio.addEventListener('change', toggleOtherGenderField);
    });
</script>
@endsection
