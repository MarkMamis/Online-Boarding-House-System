<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\AcademicCatalogService;

class StudentSetupController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isStudentSetupComplete()) {
            return redirect()->route('student.dashboard');
        }

        $checklist = $this->buildChecklist($user);
        $completedCount = collect($checklist)->where('completed', true)->count();
        $totalCount = count($checklist);
        $missingFields = $user->missingStudentSetupFields();
        $academicCatalog = AcademicCatalogService::getCatalog();

        return view('student.setup.index', compact(
            'user',
            'checklist',
            'completedCount',
            'totalCount',
            'missingFields',
            'academicCatalog'
        ));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$request->filled('program') && $request->filled('course')) {
            $request->merge(['program' => $request->input('course')]);
        }

        $isFirstYear = (string) $request->input('year_level') === '1st Year';

        $profileImageRule = filled($user->profile_image_path)
            ? 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:3072'
            : 'required|image|mimes:jpg,jpeg,png,webp,gif|max:3072';

        $schoolIdRule = $isFirstYear
            ? 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:3072'
            : (filled($user->school_id_path)
                ? 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:3072'
                : 'required|image|mimes:jpg,jpeg,png,webp,gif|max:3072');

        $enrollmentProofRule = $isFirstYear
            ? (filled($user->enrollment_proof_path)
                ? 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096'
                : 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:4096')
            : 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096';

        $enrollmentProofTypeRule = $isFirstYear
            ? 'required|in:cor,coe'
            : 'nullable|in:cor,coe';

        $academicCatalog = AcademicCatalogService::getCatalog();
        $collegeOptions = array_keys($academicCatalog['colleges'] ?? []);
        $collegeRule = empty($collegeOptions)
            ? 'required|string|max:20'
            : 'required|in:' . implode(',', $collegeOptions);

        if ($request->hasFile('enrollment_proof_file') || filled($user->enrollment_proof_path)) {
            $enrollmentProofTypeRule = 'required|in:cor,coe';
        }

        $studentIdRule = $isFirstYear
            ? 'nullable|string|max:50|unique:users,student_id,' . $user->id
            : 'required|string|max:50|unique:users,student_id,' . $user->id;

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'contact_number' => 'required|regex:/^09\d{9}$/',
            'student_id' => $studentIdRule,
            'college' => $collegeRule,
            'program' => 'required|string|max:255',
            'major' => 'nullable|string|max:255',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year',
            'gender' => 'required|in:Male,Female,Other,Rather not say',
            'gender_custom' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'required|string|max:500',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_number' => 'required|regex:/^09\d{9}$/',
            'emergency_contact_relationship' => 'required|string|max:100',
            'parent_contact_name' => 'required|string|max:255',
            'parent_contact_number' => 'required|regex:/^09\d{9}$/',
            'parent_contact_address' => 'required|string|max:500',
            'profile_image' => $profileImageRule,
            'school_id_photo' => $schoolIdRule,
            'enrollment_proof_type' => $enrollmentProofTypeRule,
            'enrollment_proof_file' => $enrollmentProofRule,
            'parent_contact_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:3072',
        ], [
            'contact_number.regex' => 'Contact number must use 11-digit PH mobile format (09XXXXXXXXX).',
            'emergency_contact_number.regex' => 'Emergency contact number must use 11-digit PH mobile format (09XXXXXXXXX).',
            'parent_contact_number.regex' => 'Parent or guardian number must use 11-digit PH mobile format (09XXXXXXXXX).',
            'student_id.required' => 'Student ID is required for 2nd year and above.',
            'enrollment_proof_file.required' => 'COR or COE file is required for 1st year students.',
        ]);

        $validator->after(function ($validator) use ($request, $academicCatalog) {
            $college = trim((string) $request->input('college', ''));
            $program = trim((string) $request->input('program', ''));
            $major = trim((string) $request->input('major', ''));
            $catalogPrograms = $academicCatalog['programs'] ?? [];
            $catalogMajors = $academicCatalog['majors'] ?? [];

            if ($college !== '' && $program !== '') {
                $allowedPrograms = $catalogPrograms[$college] ?? [];
                if (!in_array($program, $allowedPrograms, true)) {
                    $validator->errors()->add('program', 'The selected program is not valid for the selected college.');
                }
            }

            if ($major !== '') {
                $allowedMajors = $catalogMajors[$program] ?? [];
                if (!in_array($major, $allowedMajors, true)) {
                    $validator->errors()->add('major', 'The selected major is not valid for the selected program.');
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $gender = $request->input('gender');
        if ($gender === 'Other') {
            $customGender = trim((string) $request->input('gender_custom'));
            if ($customGender === '') {
                return back()
                    ->withErrors(['gender_custom' => 'Please specify your gender when choosing Other.'])
                    ->withInput();
            }
            $gender = $customGender;
        }

        if ($request->hasFile('profile_image')) {
            if (!empty($user->profile_image_path)) {
                Storage::disk('public')->delete($user->profile_image_path);
            }

            $user->profile_image_path = str_replace('\\', '/', $request->file('profile_image')->store('profiles', 'public'));
        }

        if ($request->hasFile('school_id_photo')) {
            if (!empty($user->school_id_path)) {
                Storage::disk('public')->delete($user->school_id_path);
            }

            $user->school_id_path = str_replace('\\', '/', $request->file('school_id_photo')->store('student_ids', 'public'));
        }

        if ($request->hasFile('enrollment_proof_file')) {
            if (!empty($user->enrollment_proof_path)) {
                Storage::disk('public')->delete($user->enrollment_proof_path);
            }

            $user->enrollment_proof_path = str_replace('\\', '/', $request->file('enrollment_proof_file')->store('student_enrollment_proofs', 'public'));
        }

        if ($request->filled('enrollment_proof_type')) {
            $user->enrollment_proof_type = strtolower((string) $request->input('enrollment_proof_type'));
        }

        $verificationProofUpdated = $request->hasFile('school_id_photo') || $request->hasFile('enrollment_proof_file');
        if ($verificationProofUpdated) {
            $hasAnyProof = filled($user->school_id_path) || filled($user->enrollment_proof_path);
            $user->school_id_verification_status = $hasAnyProof ? 'pending' : 'not_submitted';
            $user->school_id_verified_at = null;
            $user->school_id_verified_by = null;
            $user->school_id_rejection_reason = null;
        }

        if ($request->hasFile('parent_contact_photo')) {
            if (!empty($user->parent_contact_photo_path)) {
                Storage::disk('public')->delete($user->parent_contact_photo_path);
            }

            $user->parent_contact_photo_path = str_replace('\\', '/', $request->file('parent_contact_photo')->store('parent_contacts', 'public'));
        }

        $college = trim((string) $request->input('college', ''));
        $program = trim((string) $request->input('program', ''));
        $major = trim((string) $request->input('major', ''));

        $allowedMajors = $academicCatalog['majors'][$program] ?? [];
        if (!in_array($major, $allowedMajors, true)) {
            $major = '';
        }

        $user->fill([
            'full_name' => $request->input('full_name'),
            'name' => $request->input('full_name'),
            'contact_number' => $request->input('contact_number'),
            'student_id' => $request->filled('student_id') ? $request->input('student_id') : null,
            'college' => $college !== '' ? $college : null,
            'program' => $program,
            'major' => $major !== '' ? $major : null,
            'year_level' => $request->input('year_level'),
            'gender' => $gender,
            'birth_date' => $request->input('birth_date'),
            'address' => $request->input('address'),
            'emergency_contact_name' => $request->input('emergency_contact_name'),
            'emergency_contact_number' => $request->input('emergency_contact_number'),
            'emergency_contact_relationship' => $request->input('emergency_contact_relationship'),
            'parent_contact_name' => $request->input('parent_contact_name'),
            'parent_contact_number' => $request->input('parent_contact_number'),
            'parent_contact_address' => $request->input('parent_contact_address'),
            'enrollment_proof_type' => $request->filled('enrollment_proof_type')
                ? strtolower((string) $request->input('enrollment_proof_type'))
                : null,
        ]);

        $user->save();

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Student setup completed. Your portal is now fully unlocked.');
    }

    private function buildChecklist(\App\Models\User $user): array
    {
        return [
            [
                'title' => 'Personal profile',
                'description' => 'Set full name, profile photo, contact number, and address.',
                'completed' => filled($user->full_name)
                    && filled($user->profile_image_path)
                    && filled($user->contact_number)
                    && filled($user->address),
            ],
            [
                'title' => 'Academic verification',
                'description' => 'Set college, program, optional major, and upload your verification files (School ID; COR or COE for 1st year).',
                'completed' => (($user->year_level === '1st Year') || filled($user->student_id))
                    && filled($user->college)
                    && filled($user->program)
                    && filled($user->year_level)
                    && filled($user->gender)
                    && (($user->year_level === '1st Year')
                        ? (filled($user->enrollment_proof_type) && filled($user->enrollment_proof_path))
                        : filled($user->school_id_path)),
            ],
            [
                'title' => 'Emergency contacts',
                'description' => 'Provide emergency and parent or guardian contact details.',
                'completed' => filled($user->emergency_contact_name)
                    && filled($user->emergency_contact_number)
                    && filled($user->emergency_contact_relationship)
                    && filled($user->parent_contact_name)
                    && filled($user->parent_contact_number)
                    && filled($user->parent_contact_address),
            ],
        ];
    }
}
