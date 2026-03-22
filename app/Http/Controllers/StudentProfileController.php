<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentProfileController extends Controller
{
    // Show student profile
    public function show()
    {
        $user = Auth::user();
        return view('student.profile.show', compact('user'));
    }

    // Show edit profile form
    public function edit()
    {
        $user = Auth::user();
        return view('student.profile.edit', compact('user'));
    }

    // Update student profile
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'contact_number' => 'nullable|string|max:20',
            'student_id' => 'nullable|string|max:50|unique:users,student_id,' . $user->id,
            'course' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female',
            'year_level' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'parent_contact_name' => 'nullable|string|max:255',
            'parent_contact_number' => 'nullable|string|max:20',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_contact' => 'nullable|string|max:20',
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string|max:500',
            'medications' => 'nullable|string|max:1000',
            'medical_conditions' => 'nullable|string|max:1000',
        ]);

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

        $user->fill($request->only([
            'full_name',
            'email',
            'contact_number',
            'student_id',
            'course',
            'gender',
            'year_level',
            'birth_date',
            'address',
            'emergency_contact_name',
            'emergency_contact_number',
            'emergency_contact_relationship',
            'parent_contact_name',
            'parent_contact_number',
            'guardian_name',
            'guardian_contact',
            'blood_type',
            'allergies',
            'medications',
            'medical_conditions',
        ]));

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
