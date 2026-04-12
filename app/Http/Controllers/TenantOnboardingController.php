<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Booking;
use App\Models\TenantOnboarding;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
// use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TenantOnboardingController extends Controller
{
    // Student: view onboarding status
    public function index()
    {
        $student = Auth::user();
        $today = now()->toDateString();

        $currentApprovedBooking = $student->bookings()
            ->where('status', 'approved')
            ->where('check_out', '>', $today)
            ->with(['room.property.landlord'])
            ->orderByDesc('check_in')
            ->first();
        $hasCurrentApprovedBooking = !empty($currentApprovedBooking);

        $latestOnboarding = TenantOnboarding::query()
            ->whereHas('booking', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->with(['booking.room.property.landlord'])
            ->latest()
            ->first();

        $allOnboardings = TenantOnboarding::query()
            ->whereHas('booking', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->with(['booking.room.property.landlord'])
            ->orderByDesc('created_at')
            ->get();

        $leaveRequests = collect();
        if (Schema::hasTable('leave_requests')) {
            $leaveRequests = \App\Models\LeaveRequest::query()
                ->where('student_id', $student->id)
                ->with(['booking.room.property.landlord'])
                ->orderByDesc('created_at')
                ->get();
        }

        $currentBookingLeaveRequests = collect();
        if (!empty($currentApprovedBooking)) {
            $currentBookingLeaveRequests = $leaveRequests->where('booking_id', $currentApprovedBooking->id)->values();
        }

        return view('student.onboarding.index', compact(
            'allOnboardings',
            'latestOnboarding',
            'currentApprovedBooking',
            'hasCurrentApprovedBooking',
            'leaveRequests',
            'currentBookingLeaveRequests'
        ));
    }

    // Student: start onboarding process
    public function show(TenantOnboarding $onboarding)
    {
        $this->authorize('view', $onboarding);

        // Load necessary relationships
        $onboarding->load('booking.student', 'booking.room.property.landlord.landlordProfile');

        return view('student.onboarding.show', compact('onboarding'));
    }

    // Shared: contract document viewer for student, landlord, and admin
    public function viewContract(TenantOnboarding $onboarding)
    {
        $this->authorize('view', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property.landlord.landlordProfile');

        $role = strtolower((string) (Auth::user()->role ?? 'student'));

        $backUrl = match ($role) {
            'admin' => route('admin.onboardings.show', $onboarding),
            'landlord' => route('landlord.onboarding.review', $onboarding),
            default => route('student.onboarding.show', $onboarding),
        };

        $pdfRouteName = match ($role) {
            'admin' => 'admin.onboardings.contract_pdf',
            'landlord' => 'landlord.onboarding.contract_pdf',
            default => 'student.onboarding.contract_pdf',
        };

        $pdfPreviewUrl = route($pdfRouteName, ['onboarding' => $onboarding]);
        $pdfDownloadUrl = route($pdfRouteName, ['onboarding' => $onboarding, 'download' => 1]);

        return view('onboarding.contract_view', [
            'onboarding' => $onboarding,
            'agreementDate' => $onboarding->contract_signed_at ?: now(),
            'role' => $role,
            'backUrl' => $backUrl,
            'pdfPreviewUrl' => $pdfPreviewUrl,
            'pdfDownloadUrl' => $pdfDownloadUrl,
        ]);
    }

    // Shared: stream onboarding contract PDF (inline preview by default, optional download)
    public function downloadContractPdf(Request $request, TenantOnboarding $onboarding)
    {
        $this->authorize('view', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property.landlord.landlordProfile');

        $agreementDate = $onboarding->contract_signed_at ?: now();
        $digitalTenantId = trim((string) ($onboarding->digital_id ?? ''));
        $safeDigitalTenantId = preg_replace('/[^A-Za-z0-9\-]/', '', $digitalTenantId) ?: ('ONBOARDING-' . $onboarding->id);
        $fileName = $safeDigitalTenantId . '-ResidentialLeaseAgreement.pdf';

        $html = view('onboarding.pdf.contract_page', [
            'onboarding' => $onboarding,
            'agreementDate' => $agreementDate,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $publicPath = realpath(base_path('public'));
        if ($publicPath !== false) {
            $dompdf->setBasePath($publicPath);
        }

        $dompdf->loadHtml($html);
        $dompdf->setPaper('legal', 'portrait');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
        $canvas->page_text(
            $canvas->get_width() - 120,
            $canvas->get_height() - 26,
            'Page {PAGE_NUM} of {PAGE_COUNT}',
            $font,
            9,
            [0.2, 0.29, 0.38]
        );

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $fileName . '"',
        ]);
    }

    // Student: upload documents
    public function uploadDocuments(Request $request, TenantOnboarding $onboarding)
    {
        $this->authorize('uploadDocuments', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property.landlord');
        $requiredDocumentCount = max(1, count((array) ($onboarding->required_documents ?? [])));

        try {
            $request->validate([
                'documents' => 'required|array|min:' . $requiredDocumentCount,
                'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            ], [
                'documents.required' => 'Please upload all required documents before continuing.',
                'documents.array' => 'Invalid document upload payload. Please try again.',
                'documents.min' => 'Please upload all required documents before continuing.',
                'documents.*.required' => 'Each required document must have an uploaded file.',
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

        $this->notifyLandlordOnboardingStep(
            $onboarding,
            'Onboarding documents submitted',
            sprintf(
                '%s uploaded onboarding documents for Room %s at %s.',
                $onboarding->booking->student->full_name ?? 'A student',
                $onboarding->booking->room->room_number ?? '—',
                $onboarding->booking->room->property->name ?? 'Property'
            )
        );

        return back()->with('success', 'Documents uploaded successfully.');
    }

    // Student: sign contract
    public function signContract(Request $request, TenantOnboarding $onboarding)
    {
        $this->authorize('signContract', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property.landlord');

        if ((string) $onboarding->status !== 'documents_uploaded') {
            return back()->with('error', 'Contract can only be signed after document upload.');
        }

        $request->validate([
            'signature_data' => 'required|string',
            'signature_name' => 'nullable|string|max:255',
        ], [
            'signature_data.required' => 'Please provide your e-signature before signing the contract.',
        ]);

        // Generate contract content if not exists
        if (!$onboarding->contract_content) {
            $contract = $this->generateContract($onboarding);
            $onboarding->update(['contract_content' => $contract]);
        }

        $signaturePath = $this->storeContractSignatureDataUrl(
            (string) $request->input('signature_data'),
            $onboarding->contract_signature_path
        );

        if (!$signaturePath) {
            return back()->with('error', 'Invalid signature format. Please draw or upload your signature again.');
        }

        $signatureName = trim((string) $request->input('signature_name', ''));
        if ($signatureName === '') {
            $signatureName = trim((string) ($onboarding->booking->student->full_name ?? Auth::user()?->name ?? ''));
        }

        $onboarding->update([
            'contract_signed' => true,
            'contract_signed_at' => now(),
            'contract_signature_path' => $signaturePath,
            'contract_signature_name' => $signatureName !== '' ? $signatureName : null,
            'status' => 'contract_signed'
        ]);

        $this->notifyLandlordOnboardingStep(
            $onboarding,
            'Onboarding contract signed',
            sprintf(
                '%s signed the onboarding contract for Room %s at %s.',
                $onboarding->booking->student->full_name ?? 'A student',
                $onboarding->booking->room->room_number ?? '—',
                $onboarding->booking->room->property->name ?? 'Property'
            )
        );

        return back()->with('success', 'Contract signed successfully.');
    }

    // Student: pay deposit
    public function payDeposit(Request $request, TenantOnboarding $onboarding)
    {
        $this->authorize('payDeposit', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property.landlord.landlordProfile');

        if ((string) $onboarding->status !== 'contract_signed') {
            return back()->with('error', 'Payment can only be submitted after contract signing.');
        }

        $landlordProfile = optional(optional($onboarding->booking->room->property)->landlord)->landlordProfile;
        $preferredMethods = collect((array) ($landlordProfile?->preferred_payment_methods ?? []))
            ->map(fn ($method) => strtolower(trim((string) $method)))
            ->filter(fn ($method) => in_array($method, ['bank', 'gcash', 'cash'], true))
            ->unique()
            ->values();

        $hasBankDetails = filled($landlordProfile?->payment_bank_name)
            && filled($landlordProfile?->payment_account_name)
            && filled($landlordProfile?->payment_account_number);
        $hasGcashDetails = filled($landlordProfile?->payment_gcash_name)
            && filled($landlordProfile?->payment_gcash_number);

        $availableMethods = collect();
        if ($preferredMethods->contains('bank') && $hasBankDetails) {
            $availableMethods->push('bank');
        }
        if ($preferredMethods->contains('gcash') && $hasGcashDetails) {
            $availableMethods->push('gcash');
        }
        if ($preferredMethods->contains('cash')) {
            $availableMethods->push('cash');
        }

        // Fallback for older landlord profiles that have billing data but no preferred methods yet.
        if ($availableMethods->isEmpty()) {
            if ($hasBankDetails) {
                $availableMethods->push('bank');
            }
            if ($hasGcashDetails) {
                $availableMethods->push('gcash');
            }
        }

        if ($availableMethods->isEmpty()) {
            return back()->withInput()->with('error', 'Landlord has not configured a valid payment method yet. Please contact the landlord first.');
        }

        $validator = validator($request->all(), [
            'payment_method' => 'required|string|in:bank,gcash,cash',
            'payment_reference' => 'nullable|string|max:120',
            'payment_notes' => 'nullable|string|max:1000',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Selected payment method is invalid.',
            'payment_proof.mimes' => 'Payment proof must be a JPG, PNG, or PDF file.',
            'payment_proof.max' => 'Payment proof must not exceed 5MB.',
        ]);

        $validator->after(function ($validator) use ($request, $availableMethods, $hasBankDetails, $hasGcashDetails) {
            $selectedMethod = strtolower((string) $request->input('payment_method'));

            if ($selectedMethod && !$availableMethods->contains($selectedMethod)) {
                $validator->errors()->add('payment_method', 'This payment method is not available for this landlord.');
                return;
            }

            if (in_array($selectedMethod, ['bank', 'gcash'], true) && !filled($request->input('payment_reference'))) {
                $validator->errors()->add('payment_reference', 'Reference number is required for Bank and GCash payments.');
            }

            if (in_array($selectedMethod, ['bank', 'gcash'], true) && !$request->hasFile('payment_proof')) {
                $validator->errors()->add('payment_proof', 'Payment proof is required for Bank and GCash payments.');
            }

            if ($selectedMethod === 'bank' && !$hasBankDetails) {
                $validator->errors()->add('payment_method', 'Bank payment is currently unavailable for this landlord.');
            }

            if ($selectedMethod === 'gcash' && !$hasGcashDetails) {
                $validator->errors()->add('payment_method', 'GCash payment is currently unavailable for this landlord.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $selectedMethod = strtolower((string) $request->input('payment_method'));
        $requiresOnlineProof = in_array($selectedMethod, ['bank', 'gcash'], true);

        $paymentProofPath = $onboarding->payment_proof_path;
        if ($requiresOnlineProof && $request->hasFile('payment_proof')) {
            if (!empty($paymentProofPath)) {
                Storage::disk('public')->delete($paymentProofPath);
            }
            $paymentProofPath = str_replace('\\', '/', $request->file('payment_proof')->store('onboarding-payments', 'public'));
        }

        if (!$requiresOnlineProof) {
            if (!empty($paymentProofPath)) {
                Storage::disk('public')->delete($paymentProofPath);
            }
            $paymentProofPath = null;
        }

        $monthlyRent = is_numeric($onboarding->booking->monthly_rent_amount)
            ? (float) $onboarding->booking->monthly_rent_amount
            : (float) ($onboarding->booking->room->price ?? 0);
        $advanceAmount = !empty($onboarding->booking->include_advance_payment)
            ? $monthlyRent
            : 0.0;
        $depositAmount = $monthlyRent + $advanceAmount;

        $onboarding->update([
            'deposit_amount' => $depositAmount,
            'advance_amount' => $advanceAmount,
            'payment_method' => $selectedMethod,
            'payment_reference' => $requiresOnlineProof && $request->filled('payment_reference')
                ? trim((string) $request->input('payment_reference'))
                : null,
            'payment_proof_path' => $paymentProofPath,
            'payment_notes' => $request->filled('payment_notes') ? trim((string) $request->input('payment_notes')) : null,
            'payment_submitted_at' => now(),
            'deposit_paid' => false,
            'deposit_paid_at' => null,
            'status' => 'deposit_paid'
        ]);

        $booking = $onboarding->booking;
        $baseDueDate = $booking->resolvePaymentDueDate() ?: now()->startOfDay();
        $booking->update([
            'payment_status' => 'pending',
            'payment_date' => null,
            'next_payment_due_date' => $baseDueDate->copy()->toDateString(),
            'last_overdue_notified_at' => null,
        ]);

        $this->notifyLandlordOnboardingStep(
            $onboarding,
            'Onboarding payment submitted',
            sprintf(
                '%s submitted onboarding payment via %s for Room %s at %s. Review and approve the payment to complete onboarding.',
                $onboarding->booking->student->full_name ?? 'A student',
                ucfirst($selectedMethod),
                $onboarding->booking->room->room_number ?? '—',
                $onboarding->booking->room->property->name ?? 'Property'
            )
        );

        return back()->with('success', ucfirst($selectedMethod) . ' payment submitted successfully. Waiting for landlord approval.');
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

        $onboarding->load('booking.student', 'booking.room.property.landlord.landlordProfile');

        return view('landlord.onboarding.review', compact('onboarding'));
    }

    // Landlord: sign contract using landlord profile e-signature
    public function signContractAsLandlord(TenantOnboarding $onboarding)
    {
        $this->authorize('signContractAsLandlord', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property.landlord.landlordProfile');

        if (!$onboarding->contract_signed) {
            return back()->with('error', 'Tenant must sign the contract first before landlord signing.');
        }

        $landlord = $onboarding->booking?->room?->property?->landlord;
        $landlordProfile = $landlord?->landlordProfile;
        $profileSignaturePath = trim((string) ($landlordProfile->contract_signature_path ?? ''));

        if ($profileSignaturePath === '' || !Storage::disk('public')->exists($profileSignaturePath)) {
            return back()->with('error', 'Please upload your e-signature in Profile first before signing the contract.');
        }

        $storedSignaturePath = $this->copySignatureFromPublicDisk(
            $profileSignaturePath,
            $onboarding->landlord_contract_signature_path,
            'landlord-' . $onboarding->id
        );

        if (!$storedSignaturePath) {
            return back()->with('error', 'Unable to save landlord signature at this time. Please try again.');
        }

        $landlordSignatureName = trim((string) ($landlord->full_name ?? $landlord->name ?? 'Landlord'));

        $onboarding->update([
            'landlord_contract_signed' => true,
            'landlord_contract_signed_at' => now(),
            'landlord_contract_signature_path' => $storedSignaturePath,
            'landlord_contract_signature_name' => $landlordSignatureName !== '' ? $landlordSignatureName : null,
        ]);

        $this->notifyStudentOnboardingStep(
            $onboarding,
            'Landlord signed onboarding contract',
            sprintf(
                'Your landlord signed the onboarding contract for Room %s at %s.',
                $onboarding->booking->room->room_number ?? '—',
                $onboarding->booking->room->property->name ?? 'Property'
            )
        );

        return back()->with('success', 'Contract signed successfully as landlord.');
    }

    // Landlord: approve/reject documents
    public function approveDocuments(Request $request, TenantOnboarding $onboarding)
    {
        $this->authorize('approveDocuments', $onboarding);

        $onboarding->load('booking.student', 'booking.room.property');

        $action = (string) $request->input('action');

        if ($action === 'approve') {
            $onboarding->update(['status' => 'documents_uploaded']);
            return back()->with('success', 'Documents approved.');
        }

        if ($action === 'reject') {
            // Reset to pending for re-upload
            $onboarding->update([
                'status' => 'pending',
                'uploaded_documents' => []
            ]);
            return back()->with('error', 'Documents rejected. Student needs to re-upload.');
        }

        if ($action === 'approve_payment') {
            if ((string) $onboarding->status !== 'deposit_paid') {
                return back()->with('error', 'No payment submission is pending approval for this onboarding.');
            }

            $booking = $onboarding->booking;
            $baseDueDate = $booking->resolvePaymentDueDate() ?: now()->startOfDay();

            $onboarding->update([
                'deposit_paid' => true,
                'deposit_paid_at' => now(),
            ]);

            $booking->update([
                'payment_status' => 'paid',
                'payment_date' => now(),
                'next_payment_due_date' => $baseDueDate->copy()->addMonthNoOverflow()->toDateString(),
                'last_overdue_notified_at' => null,
            ]);

            $this->completeOnboarding($onboarding->fresh());

            $this->notifyStudentOnboardingStep(
                $onboarding,
                'Onboarding payment approved',
                sprintf(
                    'Your onboarding payment for Room %s at %s was approved. Onboarding is now complete and tenant access is active.',
                    $onboarding->booking->room->room_number ?? '—',
                    $onboarding->booking->room->property->name ?? 'Property'
                )
            );

            return back()->with('success', 'Payment approved. Onboarding is now completed and tenant access is enabled.');
        }

        if ($action === 'reject_payment') {
            if ((string) $onboarding->status !== 'deposit_paid') {
                return back()->with('error', 'No payment submission is pending rejection for this onboarding.');
            }

            if (!empty($onboarding->payment_proof_path)) {
                Storage::disk('public')->delete($onboarding->payment_proof_path);
            }

            $onboarding->update([
                'status' => 'contract_signed',
                'deposit_amount' => null,
                'advance_amount' => null,
                'payment_method' => null,
                'payment_reference' => null,
                'payment_proof_path' => null,
                'payment_notes' => null,
                'payment_submitted_at' => null,
                'deposit_paid' => false,
                'deposit_paid_at' => null,
            ]);

            $booking = $onboarding->booking;
            $baseDueDate = $booking->resolvePaymentDueDate() ?: now()->startOfDay();
            $booking->update([
                'payment_status' => 'pending',
                'payment_date' => null,
                'next_payment_due_date' => $baseDueDate->copy()->toDateString(),
                'last_overdue_notified_at' => null,
            ]);

            $this->notifyStudentOnboardingStep(
                $onboarding,
                'Onboarding payment rejected',
                sprintf(
                    'Your onboarding payment for Room %s at %s was rejected. Please resubmit your payment details to continue onboarding.',
                    $onboarding->booking->room->room_number ?? '—',
                    $onboarding->booking->room->property->name ?? 'Property'
                )
            );

            return back()->with('error', 'Payment was rejected. Student must resubmit payment details.');
        }

        return back()->with('error', 'Invalid onboarding review action.');
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

        $checkInLabel = $booking->check_in ? $booking->check_in->format('M d, Y') : 'Pending';
        $checkOutLabel = $booking->check_out ? $booking->check_out->format('M d, Y') : 'Open Ended';
        $durationDays = method_exists($booking, 'getDurationInDays') ? $booking->getDurationInDays() : 0;
        $requestedLabel = $booking->created_at ? $booking->created_at->diffForHumans() : 'Recently';
        $roomModeLabel = ucfirst((string) ($booking->occupancy_mode ?? 'Solo'));
        $advancePaymentLabel = !empty($booking->include_advance_payment) ? 'Yes' : 'No';
        $paymentStatusLabel = ucfirst((string) $booking->derivedPaymentStatus());
        $fullPaymentAmount = $room->price;

        return "RESIDENTIAL LEASE AGREEMENT
    THIS AGREEMENT (\"Agreement\") is made and entered into on this " . now()->format('jS') . " day of " . now()->format('F, Y') . ", by and between {$property->landlord->name} (\"Landlord\") and {$student->name} (\"Tenant\").

    1. PREMISES
    The Landlord agrees to rent to the Tenant, and the Tenant agrees to rent from the Landlord, the property located at: {$property->name}, Room {$room->room_number} (the \"Premises\"), under the terms and conditions set forth below.

    2. TERM AND OCCUPANCY
    The Tenant agrees to occupy the Premises under the approved booking terms and to strictly adhere to all community policies established by the Landlord. This Agreement serves as the binding contract for the tenancy commencing upon the completion of the Onboarding Process.

    3. RENT AND PAYMENT
    3.1 Due Date. Rent is due on the 1st day of each month.
    3.2 Method of Payment. All rent payments must be payable exclusively through the designated Platform.
    3.3 Onboarding Payment. The full monthly rent is required to complete the onboarding process and will be recorded as the active payment basis for the lease term.
    Monthly Rent: ₱" . number_format($room->price, 2) . "
    Full Payment Due: ₱" . number_format($fullPaymentAmount, 2) . "

    4. COMMUNITY RULES AND MAINTENANCE
    4.1 Quiet Hours. To ensure the comfort of all residents, quiet hours are enforced from 10:00 PM to 7:00 AM.
    4.2 Guests. All guests must be registered with the Landlord prior to arrival.
    4.3 Maintenance Requests. Maintenance requests must be submitted via the Platform within 24 hours of the Tenant noticing an issue.

    5. EXECUTION AND MOVE-IN CONDITIONS
    5.1 Binding Effect. This Contract becomes legally binding once all identity documents are verified and the electronic signature is submitted by both parties.
    5.2 Payment Verification. Advance payment and payment status will be reviewed and verified prior to room handover.
    5.3 Possession. Move-in is strictly subject to the confirmed check-in date and approved booking status.

    Booking Snapshot:
    Property: {$property->name}
    Room: Room {$room->room_number}
    Approved: " . ucfirst((string) ($booking->status ?? 'pending')) . "
    Check-in: {$checkInLabel}
    Check-out: {$checkOutLabel}
    Duration: {$durationDays} days
    Requested: {$requestedLabel}
    Room Mode: {$roomModeLabel}
    Monthly Rent: ₱" . number_format($room->price, 2) . "
    Advance Payment: {$advancePaymentLabel}
    Payment Status: {$paymentStatusLabel}

    IN WITNESS WHEREOF, the parties have executed this Agreement as of the date first written above.
    LANDLORD SIGNATURE
    Name: {$property->landlord->name}
    Date: Pending

    TENANT SIGNATURE
    Name: {$student->name}
    Date: Pending";
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

    private function storeContractSignatureDataUrl(string $signatureData, ?string $oldPath = null): ?string
    {
        $signatureData = trim($signatureData);
        if ($signatureData === '') {
            return null;
        }

        if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/i', $signatureData, $matches)) {
            return null;
        }

        $mime = strtolower((string) $matches[1]);
        $encoded = (string) $matches[2];
        $binary = base64_decode($encoded, true);

        if ($binary === false || $binary === '') {
            return null;
        }

        if (strlen($binary) > (5 * 1024 * 1024)) {
            return null;
        }

        $extension = $mime === 'jpg' ? 'jpeg' : $mime;
        $path = 'contract-signatures/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($path, $binary);

        if (!empty($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $path;
    }

    private function copySignatureFromPublicDisk(string $sourcePath, ?string $oldPath = null, string $prefix = 'signature'): ?string
    {
        $sourcePath = trim($sourcePath);
        if ($sourcePath === '' || !Storage::disk('public')->exists($sourcePath)) {
            return null;
        }

        $extension = strtolower((string) pathinfo($sourcePath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            $extension = 'png';
        }

        $targetPath = 'contract-signatures/' . $prefix . '-' . Str::uuid() . '.' . $extension;
        $copied = Storage::disk('public')->copy($sourcePath, $targetPath);

        if (!$copied || !Storage::disk('public')->exists($targetPath)) {
            return null;
        }

        if (!empty($oldPath) && $oldPath !== $sourcePath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $targetPath;
    }

    private function notifyLandlordOnboardingStep(TenantOnboarding $onboarding, string $title, string $message): void
    {
        try {
            $onboarding->loadMissing('booking.room.property.landlord');
            $landlord = $onboarding->booking?->room?->property?->landlord;

            if (!$landlord) {
                return;
            }

            $landlord->notify(new SystemNotification(
                $title,
                $message,
                route('landlord.onboarding.review', $onboarding),
                [
                    'onboarding_id' => $onboarding->id,
                    'booking_id' => $onboarding->booking_id,
                    'student_id' => $onboarding->booking?->student_id,
                ]
            ));
        } catch (\Throwable $e) {
            // Ignore notification delivery errors to avoid blocking onboarding actions.
        }
    }

    private function notifyStudentOnboardingStep(TenantOnboarding $onboarding, string $title, string $message): void
    {
        try {
            $onboarding->loadMissing('booking.student');
            $student = $onboarding->booking?->student;

            if (!$student) {
                return;
            }

            $student->notify(new SystemNotification(
                $title,
                $message,
                route('student.onboarding.show', $onboarding),
                [
                    'onboarding_id' => $onboarding->id,
                    'booking_id' => $onboarding->booking_id,
                    'student_id' => $student->id,
                ]
            ));
        } catch (\Throwable $e) {
            // Ignore notification delivery errors to avoid blocking onboarding actions.
        }
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
    public function adminIndex(Request $request)
    {
        $this->ensureAdmin();

        $status = strtolower((string) $request->query('status', 'all'));
        $search = trim((string) $request->query('search', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = TenantOnboarding::with('booking.student', 'booking.room.property.landlord')
            ->orderBy('created_at', 'desc');

        if ($status === 'active') {
            $query->whereIn('status', [
                'documents_uploaded',
                'documents_approved',
                'contract_signed',
                'deposit_paid',
            ]);
        } elseif ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } else {
            $status = 'all';
        }

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                if (ctype_digit($search)) {
                    $inner->orWhere('id', (int) $search);
                }

                $like = '%' . $search . '%';

                $inner->orWhere('status', 'like', $like)
                    ->orWhere('digital_id', 'like', $like)
                    ->orWhereHas('booking.student', function ($studentQuery) use ($like) {
                        $studentQuery->where('full_name', 'like', $like)
                            ->orWhere('student_id', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    })
                    ->orWhereHas('booking.room', function ($roomQuery) use ($like) {
                        $roomQuery->where('room_number', 'like', $like);
                    })
                    ->orWhereHas('booking.room.property', function ($propertyQuery) use ($like) {
                        $propertyQuery->where('name', 'like', $like)
                            ->orWhere('address', 'like', $like);
                    })
                    ->orWhereHas('booking.room.property.landlord', function ($landlordQuery) use ($like) {
                        $landlordQuery->where('full_name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
            });
        }

        if (is_string($dateFrom) && $dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (is_string($dateTo) && $dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $onboardings = $query->paginate(20)->withQueryString();

        return view('admin.onboardings.index', compact('onboardings', 'status', 'search', 'dateFrom', 'dateTo'));
    }

    // Admin: view active onboardings
    public function adminActive()
    {
        $this->ensureAdmin();

        $onboardings = TenantOnboarding::whereIn('status', [
                'documents_uploaded',
                'documents_approved',
                'contract_signed',
                'deposit_paid',
            ])
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
        return $this->viewContract($onboarding);
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
