<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TenantOnboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
// use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TenantOnboardingController extends Controller
{
    // Student: view onboarding status
    public function index()
    {
        $user = Auth::user();
        $onboardings = TenantOnboarding::whereHas('booking', function ($q) use ($user) {
            $q->where('student_id', $user->id);
        })->with('booking.room.property')->get();

        return view('student.onboarding.index', compact('onboardings'));
    }

    // Student: start onboarding process
    public function show(TenantOnboarding $onboarding)
    {
        $this->authorize('view', $onboarding);

        // Load necessary relationships
        $onboarding->load('booking.student', 'booking.room.property');

        return view('student.onboarding.show', compact('onboarding'));
    }

    // Student: upload documents
    public function uploadDocuments(Request $request, TenantOnboarding $onboarding)
    {
        $this->authorize('uploadDocuments', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property');

        try {
            $request->validate([
                'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            ]);
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return back()->withInput()->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try smaller file(s) if needed (PHP upload limit).');
        }

        $uploadedPaths = $onboarding->uploaded_documents ?? [];

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                try {
                    $path = $file->store('tenant-documents', 'public');
                    $uploadedPaths[] = $path;
                } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                    return back()->withInput()->with('error', 'Unable to process the uploaded file(s). Please ensure the PHP "fileinfo" extension is enabled and try smaller file(s) if needed (PHP upload limit).');
                }
            }
        }

        $onboarding->update([
            'uploaded_documents' => $uploadedPaths,
            'status' => 'documents_uploaded'
        ]);

        return back()->with('success', 'Documents uploaded successfully.');
    }

    // Student: sign contract
    public function signContract(TenantOnboarding $onboarding)
    {
        $this->authorize('signContract', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property');

        // Generate contract content if not exists
        if (!$onboarding->contract_content) {
            $contract = $this->generateContract($onboarding);
            $onboarding->update(['contract_content' => $contract]);
        }

        $onboarding->update([
            'contract_signed' => true,
            'contract_signed_at' => now(),
            'status' => 'contract_signed'
        ]);

        return back()->with('success', 'Contract signed successfully.');
    }

    // Student: pay deposit
    public function payDeposit(TenantOnboarding $onboarding)
    {
        $this->authorize('payDeposit', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property');

        // In a real app, integrate with payment gateway
        // For now, simulate payment
        $depositAmount = $onboarding->booking->room->price * 0.5; // 50% deposit

        $onboarding->update([
            'deposit_amount' => $depositAmount,
            'deposit_paid' => true,
            'deposit_paid_at' => now(),
            'status' => 'deposit_paid'
        ]);

        // Complete onboarding
        $this->completeOnboarding($onboarding);

        return back()->with('success', 'Deposit paid successfully. Onboarding completed!');
    }

    // Landlord: view tenant onboarding
    public function landlordIndex()
    {
        $user = Auth::user();
        $onboardings = TenantOnboarding::whereHas('booking.room.property', function ($q) use ($user) {
            $q->where('landlord_id', $user->id);
        })->with('booking.student', 'booking.room.property')->get();

        return view('landlord.onboarding.index', compact('onboardings'));
    }

    // Landlord: review documents
    public function reviewDocuments(TenantOnboarding $onboarding)
    {
        $this->authorize('reviewDocuments', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property');

        return view('landlord.onboarding.review', compact('onboarding'));
    }

    // Landlord: approve/reject documents
    public function approveDocuments(Request $request, TenantOnboarding $onboarding)
    {
        $this->authorize('approveDocuments', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property');

        $action = $request->input('action');

        if ($action === 'approve') {
            $onboarding->update(['status' => 'documents_uploaded']);
            return back()->with('success', 'Documents approved.');
        } else {
            // Reset to pending for re-upload
            $onboarding->update([
                'status' => 'pending',
                'uploaded_documents' => []
            ]);
            return back()->with('error', 'Documents rejected. Student needs to re-upload.');
        }
    }

    // Secure document viewing
    public function viewDocument(TenantOnboarding $onboarding, $filename)
    {
        $this->authorize('viewDocument', $onboarding);
        // Load necessary relationships
        $onboarding->load('booking.student', 'booking.room.property');

        // Check if the file exists in the onboarding documents
        $documents = $onboarding->uploaded_documents ?? [];
        $filePath = null;

        foreach ($documents as $doc) {
            if (basename($doc) === $filename) {
                $filePath = $doc;
                break;
            }
        }

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'Document not found');
        }

        // Return the file with appropriate headers
        $isDownload = request('download') == '1';
        return Storage::disk('public')->response($filePath, $filename, [
            'Content-Disposition' => $isDownload ? 'attachment; filename="' . $filename . '"' : 'inline'
        ]);
    }

    private function generateContract(TenantOnboarding $onboarding)
    {
        // Load all necessary relationships
        $onboarding->load('booking.student', 'booking.room.property.landlord');

        $booking = $onboarding->booking;
        $student = $booking->student;
        $room = $booking->room;
        $property = $room->property;

        return "TENANCY AGREEMENT

This Tenancy Agreement is made on " . now()->format('F d, Y') . "

BETWEEN:
Landlord: {$property->landlord->name}
Property: {$property->name}
Address: {$property->address}

AND
Tenant: {$student->name}
Student ID: {$student->student_id}

Room: {$room->room_number}
Monthly Rent: ₱" . number_format($room->price, 2) . "
Lease Period: {$booking->check_in->format('M d, Y')} to {$booking->check_out->format('M d, Y')}

Terms and Conditions:
1. The Tenant agrees to pay rent on time.
2. The Tenant agrees to maintain the property.
3. The Landlord agrees to maintain habitable conditions.

Signed by Tenant: " . ($onboarding->contract_signed ? $student->name : 'Pending') . "
Date: " . ($onboarding->contract_signed_at ? $onboarding->contract_signed_at->format('M d, Y') : 'Pending');
    }

    private function completeOnboarding(TenantOnboarding $onboarding)
    {
        // Generate QR code (placeholder - implement when GD extension is available)
        // $qrData = json_encode([
        //     'tenant_id' => $onboarding->booking->student_id,
        //     'booking_id' => $onboarding->booking->id,
        //     'room' => $onboarding->booking->room->room_number,
        //     'property' => $onboarding->booking->room->property->name,
        //     'valid_until' => $onboarding->booking->check_out->format('Y-m-d')
        // ]);
        // $qrCode = QrCode::format('png')->size(200)->generate($qrData);
        // $qrPath = 'qr-codes/' . Str::uuid() . '.png';
        // Storage::disk('public')->put($qrPath, $qrCode);

        // Generate digital ID
        $digitalId = 'TID-' . strtoupper(Str::random(8));

        $onboarding->update([
            // 'qr_code_path' => $qrPath,
            'digital_id' => $digitalId,
            'status' => 'completed'
        ]);
    }

    // Helper methods
    private function ensureStudent(TenantOnboarding $onboarding)
    {
        // Allow admins to view any onboarding
        if (Auth::user()->role === 'admin') {
            return;
        }

        if ($onboarding->booking->student_id !== Auth::id()) {
            abort(403, 'Unauthorized access to onboarding process');
        }
    }

    // Admin: view all onboardings
    public function adminIndex()
    {
        $this->ensureAdmin();

        $onboardings = TenantOnboarding::with('booking.student', 'booking.room.property.landlord')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.onboardings.index', compact('onboardings'));
    }

    // Admin: view active onboardings
    public function adminActive()
    {
        $this->ensureAdmin();

        $onboardings = TenantOnboarding::whereNotIn('status', ['completed', 'cancelled'])
            ->with('booking.student', 'booking.room.property.landlord')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.onboardings.active', compact('onboardings'));
    }

    // Admin: view pending document onboardings
    public function adminPending()
    {
        $this->ensureAdmin();

        $onboardings = TenantOnboarding::where('status', 'pending')
            ->with('booking.student', 'booking.room.property.landlord')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.onboardings.pending', compact('onboardings'));
    }

    // Admin: view completed onboardings
    public function adminCompleted()
    {
        $this->ensureAdmin();

        $onboardings = TenantOnboarding::where('status', 'completed')
            ->with('booking.student', 'booking.room.property.landlord')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.onboardings.completed', compact('onboardings'));
    }

    // Admin: view specific onboarding details
    public function adminShow(TenantOnboarding $onboarding)
    {
        $this->ensureAdmin();

        $onboarding->load('booking.student', 'booking.room.property.landlord');

        return view('admin.onboardings.show', compact('onboarding'));
    }

    // Admin: view contract for onboarding
    public function adminViewContract(TenantOnboarding $onboarding)
    {
        $this->ensureAdmin();

        $contract = $this->generateContract($onboarding);

        return view('admin.onboardings.contract', compact('onboarding', 'contract'));
    }

    private function ensureLandlord(TenantOnboarding $onboarding)
    {
        if ($onboarding->booking->room->property->landlord_id !== Auth::id()) {
            abort(403, 'Unauthorized access to tenant onboarding');
        }
    }

    private function ensureAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access to admin onboarding management');
        }
    }
}
