<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\AcademicCatalogService;

class StudentProfileController extends Controller
{
    // Show student profile
    public function show()
    {
        $user = Auth::user();
        $academicCatalog = AcademicCatalogService::getCatalog();

        return view('student.profile.show', compact('user', 'academicCatalog'));
    }

    // Show edit profile form
    public function edit()
    {
        $user = Auth::user();
        $academicCatalog = AcademicCatalogService::getCatalog();

        return view('student.profile.edit', compact('user', 'academicCatalog'));
    }

    // Update student profile
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$request->filled('program') && $request->filled('course')) {
            $request->merge(['program' => $request->input('course')]);
        }

        $academicCatalog = AcademicCatalogService::getCatalog();
        $collegeOptions = array_keys($academicCatalog['colleges'] ?? []);
        $collegeRule = empty($collegeOptions)
            ? 'nullable|string|max:20'
            : 'nullable|in:' . implode(',', $collegeOptions);

        $genderInput = trim((string) $request->input('gender', ''));
        $genderMap = [
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            'rather not say' => 'Rather not say',
        ];
        $normalizedGender = null;

        if ($genderInput !== '') {
            $genderKey = strtolower($genderInput);
            if (array_key_exists($genderKey, $genderMap)) {
                $normalizedGender = $genderMap[$genderKey];
            }
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'contact_number' => 'nullable|string|max:20',
            'student_id' => 'nullable|string|max:50|unique:users,student_id,' . $user->id,
            'college' => $collegeRule,
            'program' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:50',
            'gender_custom' => 'nullable|string|max:100',
            'year_level' => 'nullable|in:1st Year,2nd Year,3rd Year,4th Year',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'school_id_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:3072',
            'enrollment_proof_type' => 'nullable|in:cor,coe',
            'enrollment_proof_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'parent_contact_name' => 'nullable|string|max:255',
            'parent_contact_number' => 'nullable|string|max:20',
            'parent_contact_address' => 'nullable|string|max:500',
            'parent_contact_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:3072',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_contact' => 'nullable|string|max:20',
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string|max:500',
            'medications' => 'nullable|string|max:1000',
            'medical_conditions' => 'nullable|string|max:1000',
        ], [
            'gender.max' => 'Gender must not exceed 50 characters.',
        ]);

        $validator->after(function ($validator) use ($genderInput, $normalizedGender, $request, $academicCatalog) {
            if ($genderInput !== '' && $normalizedGender === null) {
                $validator->errors()->add('gender', 'Please select a valid gender option.');
                return;
            }

            if ($normalizedGender === 'Other') {
                $customGender = trim((string) $request->input('gender_custom', ''));
                if ($customGender === '') {
                    $validator->errors()->add('gender_custom', 'Please specify your gender when choosing Other.');
                }
            }

            $college = trim((string) $request->input('college', ''));
            $program = trim((string) $request->input('program', ''));
            $major = trim((string) $request->input('major', ''));
            $catalogPrograms = $academicCatalog['programs'] ?? [];
            $catalogMajors = $academicCatalog['majors'] ?? [];

            if ($program !== '') {
                if ($college === '') {
                    $inferredCollege = AcademicCatalogService::inferCollegeByProgram($program);
                    if ($inferredCollege === null) {
                        $validator->errors()->add('program', 'Please select a valid program from the academic mapping.');
                    }
                } else {
                    $allowedPrograms = $catalogPrograms[$college] ?? [];
                    if (!in_array($program, $allowedPrograms, true)) {
                        $validator->errors()->add('program', 'The selected program is not valid for the selected college.');
                    }
                }
            }

            if ($major !== '') {
                $allowedMajors = $catalogMajors[$program] ?? [];
                if (!in_array($major, $allowedMajors, true)) {
                    $validator->errors()->add('major', 'The selected major is not valid for the selected program.');
                }
            }

            if ($request->hasFile('enrollment_proof_file') && !$request->filled('enrollment_proof_type')) {
                $validator->errors()->add('enrollment_proof_type', 'Please select COR or COE when uploading enrollment proof.');
            }
        });

        try {
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return back()->withInput()->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
        }

        if ($request->hasFile('profile_image')) {
            if (!empty($user->profile_image_path)) {
                Storage::disk('public')->delete($user->profile_image_path);
            }

            try {
                $user->profile_image_path = str_replace('\\', '/', $request->file('profile_image')->store('profiles', 'public'));
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }

        if ($request->hasFile('parent_contact_photo')) {
            if (!empty($user->parent_contact_photo_path)) {
                Storage::disk('public')->delete($user->parent_contact_photo_path);
            }

            try {
                $user->parent_contact_photo_path = str_replace('\\', '/', $request->file('parent_contact_photo')->store('parent_contacts', 'public'));
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }

        if ($request->hasFile('school_id_photo')) {
            if (!empty($user->school_id_path)) {
                Storage::disk('public')->delete($user->school_id_path);
            }

            try {
                $user->school_id_path = str_replace('\\', '/', $request->file('school_id_photo')->store('student_ids', 'public'));
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded School ID. Please ensure the PHP "fileinfo" extension is enabled and try again.');
            }
        }

        if ($request->hasFile('enrollment_proof_file')) {
            if (!empty($user->enrollment_proof_path)) {
                Storage::disk('public')->delete($user->enrollment_proof_path);
            }

            try {
                $user->enrollment_proof_path = str_replace('\\', '/', $request->file('enrollment_proof_file')->store('student_enrollment_proofs', 'public'));
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded enrollment proof. Please ensure the PHP "fileinfo" extension is enabled and try again.');
            }
        }

        if ($request->filled('enrollment_proof_type')) {
            $user->enrollment_proof_type = strtolower((string) $request->input('enrollment_proof_type'));
        }

        $college = trim((string) $request->input('college', ''));
        $program = trim((string) $request->input('program', ''));
        $major = trim((string) $request->input('major', ''));

        if ($college === '' && $program !== '') {
            $college = (string) (AcademicCatalogService::inferCollegeByProgram($program) ?? '');
        }

        $verificationProofUpdated = $request->hasFile('school_id_photo') || $request->hasFile('enrollment_proof_file');
        if ($verificationProofUpdated) {
            $hasAnyProof = filled($user->school_id_path) || filled($user->enrollment_proof_path);
            $user->school_id_verification_status = $hasAnyProof ? 'pending' : 'not_submitted';
            $user->school_id_verified_at = null;
            $user->school_id_verified_by = null;
            $user->school_id_rejection_reason = null;
        }

        $user->fill($request->only([
            'full_name',
            'email',
            'contact_number',
            'student_id',
            'year_level',
            'birth_date',
            'address',
            'emergency_contact_name',
            'emergency_contact_number',
            'emergency_contact_relationship',
            'parent_contact_name',
            'parent_contact_number',
            'parent_contact_address',
            'guardian_name',
            'guardian_contact',
            'blood_type',
            'allergies',
            'medications',
            'medical_conditions',
        ]));

        $user->name = $request->input('full_name');
        $user->college = $college !== '' ? $college : null;
        $user->program = $program !== '' ? $program : null;
        $user->major = $major !== '' ? $major : null;

        if ($normalizedGender === 'Other') {
            $user->gender = trim((string) $request->input('gender_custom'));
        } elseif ($normalizedGender !== null) {
            $user->gender = $normalizedGender;
        } else {
            $user->gender = null;
        }

        $user->save();

        return redirect()->route('student.profile.show')->with('success', 'Profile updated successfully!');
    }

    // Show change password form
    public function editPassword()
    {
        return view('student.profile.change-password');
    }

    // Update password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('student.profile.show')->with('success', 'Password changed successfully!');
    }
}
