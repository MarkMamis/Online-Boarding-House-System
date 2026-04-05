<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TenantPayment;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentPaymentController extends Controller
{
    protected function ensureStudent(): void
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403, 'Unauthorized');
        }
    }

    public function index(Request $request)
    {
        $this->ensureStudent();

        $student = Auth::user();
        $today = now()->toDateString();

        $tenantBookings = Booking::query()
            ->with(['room.property.landlord.landlordProfile', 'tenantOnboarding'])
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->where('check_in', '<=', $today)
            ->where('check_out', '>', $today)
            ->whereHas('tenantOnboarding', function ($q) {
                $q->where('status', 'completed');
            })
            ->orderBy('next_payment_due_date')
            ->orderByDesc('check_in')
            ->get();

        $selectedBookingId = (int) $request->query('booking_id', 0);
        $selectedBooking = $tenantBookings->firstWhere('id', $selectedBookingId) ?: $tenantBookings->first();

        $viewMode = strtolower((string) $request->query('view', 'list'));
        if (!in_array($viewMode, ['list', 'submit'], true)) {
            $viewMode = 'list';
        }

        $paymentRecordsQuery = TenantPayment::query()
            ->with(['booking.room.property'])
            ->where('student_id', $student->id)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        if ($selectedBooking) {
            $paymentRecordsQuery->where('booking_id', $selectedBooking->id);
        }

        $paymentRecords = $paymentRecordsQuery->paginate(10)->withQueryString();

        $onboardingPaymentRecord = null;
        if ($selectedBooking && $selectedBooking->tenantOnboarding) {
            $onboarding = $selectedBooking->tenantOnboarding;
            $hasOnboardingPaymentData = (
                (is_numeric($onboarding->deposit_amount) && (float) $onboarding->deposit_amount > 0)
                || filled($onboarding->payment_method)
                || filled($onboarding->payment_reference)
                || !empty($onboarding->payment_proof_path)
                || !empty($onboarding->payment_submitted_at)
                || !empty($onboarding->deposit_paid_at)
            );

            if ($hasOnboardingPaymentData) {
                $onboardingStatus = $onboarding->deposit_paid
                    ? 'approved'
                    : (((string) $onboarding->status === 'deposit_paid' || !empty($onboarding->payment_submitted_at))
                        ? 'submitted'
                        : 'rejected');

                $onboardingPaymentRecord = (object) [
                    'record_type' => 'onboarding',
                    'label' => 'Initial Onboarding Payment',
                    'status' => $onboardingStatus,
                    'billing_for_date' => $selectedBooking->check_in,
                    'due_date' => $selectedBooking->check_in,
                    'amount_due' => (float) ($onboarding->deposit_amount ?? 0),
                    'payment_method' => $onboarding->payment_method,
                    'payment_reference' => $onboarding->payment_reference,
                    'payment_proof_path' => $onboarding->payment_proof_path,
                    'payment_notes' => $onboarding->payment_notes,
                    'submitted_at' => $onboarding->payment_submitted_at,
                    'reviewed_at' => $onboarding->deposit_paid_at,
                    'created_at' => $onboarding->created_at,
                ];
            }
        }

        $nextDueDate = null;
        $nextDueAmount = 0.0;
        $availablePaymentMethods = collect();
        $landlordProfile = null;
        $hasSubmittedForDue = false;

        if ($selectedBooking) {
            $nextDueDate = $selectedBooking->resolvePaymentDueDate() ?: now()->startOfDay();
            $nextDueAmount = is_numeric($selectedBooking->monthly_rent_amount)
                ? (float) $selectedBooking->monthly_rent_amount
                : (float) ($selectedBooking->room->price ?? 0);

            $landlordProfile = optional(optional($selectedBooking->room->property)->landlord)->landlordProfile;
            $availablePaymentMethods = $this->resolveAvailablePaymentMethods($landlordProfile);

            $hasSubmittedForDue = TenantPayment::query()
                ->where('booking_id', $selectedBooking->id)
                ->whereDate('billing_for_date', optional($nextDueDate)->toDateString())
                ->whereIn('status', ['submitted', 'approved'])
                ->exists();
        }

        $totalPaid = TenantPayment::query()
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->sum('amount_due');

        $totalPending = TenantPayment::query()
            ->where('student_id', $student->id)
            ->where('status', 'submitted')
            ->sum('amount_due');

        $pendingCount = TenantPayment::query()
            ->where('student_id', $student->id)
            ->where('status', 'submitted')
            ->count();

        if ($tenantBookings->isEmpty()) {
            $viewMode = 'list';
        }

        return view('student.payments.index', compact(
            'tenantBookings',
            'selectedBooking',
            'viewMode',
            'paymentRecords',
            'nextDueDate',
            'nextDueAmount',
            'availablePaymentMethods',
            'landlordProfile',
            'hasSubmittedForDue',
            'onboardingPaymentRecord',
            'totalPaid',
            'totalPending',
            'pendingCount'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureStudent();

        $student = Auth::user();
        $today = now()->toDateString();

        $request->validate([
            'booking_id' => 'required|integer',
        ]);

        $booking = Booking::query()
            ->with(['room.property.landlord.landlordProfile', 'tenantOnboarding'])
            ->where('id', (int) $request->input('booking_id'))
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->where('check_in', '<=', $today)
            ->where('check_out', '>', $today)
            ->whereHas('tenantOnboarding', function ($q) {
                $q->where('status', 'completed');
            })
            ->firstOrFail();

        $landlordProfile = optional(optional($booking->room->property)->landlord)->landlordProfile;
        $availablePaymentMethods = $this->resolveAvailablePaymentMethods($landlordProfile);

        if ($availablePaymentMethods->isEmpty()) {
            return back()->withInput()->with('error', 'Landlord has not configured a valid payment method yet.');
        }

        $nextDueDate = $booking->resolvePaymentDueDate() ?: now()->startOfDay();
        $billingDate = $nextDueDate->copy()->startOfDay();

        $hasSubmittedForDue = TenantPayment::query()
            ->where('booking_id', $booking->id)
            ->whereDate('billing_for_date', $billingDate->toDateString())
            ->whereIn('status', ['submitted', 'approved'])
            ->exists();

        if ($hasSubmittedForDue) {
            return back()->with('error', 'A payment submission already exists for this billing month.');
        }

        $hasBankDetails = filled($landlordProfile?->payment_bank_name)
            && filled($landlordProfile?->payment_account_name)
            && filled($landlordProfile?->payment_account_number);
        $hasGcashDetails = filled($landlordProfile?->payment_gcash_name)
            && filled($landlordProfile?->payment_gcash_number);

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

        $validator->after(function ($validator) use ($request, $availablePaymentMethods, $hasBankDetails, $hasGcashDetails) {
            $selectedMethod = strtolower((string) $request->input('payment_method'));

            if ($selectedMethod && !$availablePaymentMethods->contains($selectedMethod)) {
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

        $paymentProofPath = null;
        if ($requiresOnlineProof && $request->hasFile('payment_proof')) {
            $paymentProofPath = str_replace('\\', '/', $request->file('payment_proof')->store('tenant-payments', 'public'));
        }

        $monthlyRentAmount = is_numeric($booking->monthly_rent_amount)
            ? (float) $booking->monthly_rent_amount
            : (float) ($booking->room->price ?? 0);

        $payment = TenantPayment::create([
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'billing_for_date' => $billingDate->toDateString(),
            'due_date' => optional($nextDueDate)->toDateString(),
            'amount_due' => $monthlyRentAmount,
            'payment_method' => $selectedMethod,
            'payment_reference' => $requiresOnlineProof && $request->filled('payment_reference')
                ? trim((string) $request->input('payment_reference'))
                : null,
            'payment_proof_path' => $paymentProofPath,
            'payment_notes' => $request->filled('payment_notes') ? trim((string) $request->input('payment_notes')) : null,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $booking->update([
            'payment_status' => 'pending',
            'payment_date' => null,
            'last_overdue_notified_at' => null,
        ]);

        $this->notifyLandlordOnMonthlyPaymentSubmission($booking, $student->id, $payment, $selectedMethod, $billingDate->toDateString());

        return back()->with('success', 'Monthly payment submitted successfully. Waiting for landlord verification.');
    }

    private function resolveAvailablePaymentMethods($landlordProfile)
    {
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

        if ($availableMethods->isEmpty()) {
            if ($hasBankDetails) {
                $availableMethods->push('bank');
            }
            if ($hasGcashDetails) {
                $availableMethods->push('gcash');
            }
        }

        return $availableMethods;
    }

    private function notifyLandlordOnMonthlyPaymentSubmission(Booking $booking, int $studentId, TenantPayment $payment, string $selectedMethod, string $billingDate): void
    {
        $booking->loadMissing('room.property.landlord', 'student');

        $landlord = $booking->room?->property?->landlord;
        if (!$landlord) {
            Log::warning('Monthly payment submitted but landlord was not resolved for notification.', [
                'booking_id' => $booking->id,
                'student_id' => $studentId,
                'tenant_payment_id' => $payment->id,
            ]);
            return;
        }

        $title = 'Monthly payment submitted';
        $message = sprintf(
            '%s submitted monthly payment (%s) for Room %s due %s. Review payment details in Payments.',
            $booking->student?->full_name ?? 'A student',
            ucfirst($selectedMethod),
            $booking->room?->room_number ?? '—',
            Carbon::parse($billingDate)->format('M d, Y')
        );
        $url = route('landlord.payments.index');
        $meta = [
            'booking_id' => $booking->id,
            'student_id' => $studentId,
            'tenant_payment_id' => $payment->id,
        ];

        try {
            $landlord->notify(new SystemNotification($title, $message, $url, $meta));
        } catch (\Throwable $e) {
            Log::warning('Primary landlord payment notification dispatch failed. Applying DB fallback.', [
                'booking_id' => $booking->id,
                'student_id' => $studentId,
                'tenant_payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            try {
                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'type' => SystemNotification::class,
                    'notifiable_type' => get_class($landlord),
                    'notifiable_id' => $landlord->getKey(),
                    'data' => json_encode([
                        'title' => $title,
                        'message' => $message,
                        'url' => $url,
                        'meta' => $meta,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $fallbackError) {
                Log::error('Failed to create fallback landlord payment notification record.', [
                    'booking_id' => $booking->id,
                    'student_id' => $studentId,
                    'tenant_payment_id' => $payment->id,
                    'error' => $fallbackError->getMessage(),
                ]);
            }
        }
    }
}
