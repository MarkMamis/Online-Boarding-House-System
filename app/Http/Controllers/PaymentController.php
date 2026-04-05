<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\LandlordProfile;
use App\Models\TenantPayment;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    protected function ensureLandlord()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403, 'Unauthorized');
        }
    }

    private function resolveStatusFilter(?string $status): string
    {
        $status = strtolower(trim((string) $status));
        return in_array($status, ['all', 'paid', 'pending', 'overdue'], true) ? $status : 'all';
    }

    public function index(Request $request)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();
        $statusFilter = $this->resolveStatusFilter($request->query('status'));
        $today = now();

        // Get approved bookings with payment status
        $bookings = Booking::with([
                'room.property',
                'student',
                'tenantOnboarding',
                'latestSubmittedTenantPayment',
                'latestTenantPayment',
            ])
            ->where('status', 'approved')
            ->where('check_out', '>', $today->toDateString())
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->orderBy('check_in', 'desc')
            ->get();

        $landlordProfile = Auth::user()->loadMissing('landlordProfile')->landlordProfile;

        $bookings->each(function (Booking $booking) use ($landlordProfile, $today) {
            $effectiveStatus = $booking->derivedPaymentStatus($today);
            $effectiveDueDate = $booking->resolvePaymentDueDate();
            $booking->effective_payment_status = $effectiveStatus;
            $booking->effective_due_date = $effectiveDueDate;
            $booking->is_overdue = $effectiveStatus === 'overdue';
            $booking->billing_amount = $this->resolveBookingBillingAmount($booking);
            $booking->latest_tenant_payment = $booking->latestSubmittedTenantPayment ?: $booking->latestTenantPayment;

            $qrPayload = $this->buildGcashQrPayload($landlordProfile, $booking);
            $booking->reminder_qr_url = $qrPayload === null
                ? null
                : $this->buildGcashQrUrl($qrPayload);
        });

        $paidCount = $bookings->where('effective_payment_status', 'paid')->count();
        $pendingCount = $bookings->where('effective_payment_status', 'pending')->count();
        $overdueCount = $bookings->where('effective_payment_status', 'overdue')->count();

        $paidBookings = $bookings->where('effective_payment_status', 'paid');
        $pendingPayments = $bookings->filter(function ($booking) {
            return in_array((string) $booking->effective_payment_status, ['pending', 'overdue'], true);
        });

        // Shared ledger summary: onboarding billing + monthly payment records.
        $onboardingPaidTotal = 0.0;
        $onboardingPendingTotal = 0.0;
        $monthlyPaidTotal = 0.0;
        $monthlyPendingTotal = 0.0;

        foreach ($bookings as $booking) {
            $onboarding = $booking->tenantOnboarding;
            if ($onboarding) {
                $onboardingAmount = is_numeric($onboarding->deposit_amount) && (float) $onboarding->deposit_amount > 0
                    ? (float) $onboarding->deposit_amount
                    : $this->resolveBookingBillingAmount($booking);

                $onboardingPaid = (bool) $onboarding->deposit_paid || (string) $onboarding->status === 'completed';
                $onboardingPending = (string) $onboarding->status === 'deposit_paid' && !(bool) $onboarding->deposit_paid;

                if ($onboardingPaid) {
                    $onboardingPaidTotal += $onboardingAmount;
                } elseif ($onboardingPending) {
                    $onboardingPendingTotal += $onboardingAmount;
                }
            }

            $monthlyPaidTotal += (float) $booking->tenantPayments
                ->where('status', 'approved')
                ->sum('amount_due');

            $monthlyPendingTotal += (float) $booking->tenantPayments
                ->where('status', 'submitted')
                ->sum('amount_due');
        }

        $totalPaid = $onboardingPaidTotal + $monthlyPaidTotal;
        $totalPending = $onboardingPendingTotal + $monthlyPendingTotal;
        $totalExpected = $totalPaid + $totalPending;
        $onboardingLedgerTotal = $onboardingPaidTotal + $onboardingPendingTotal;
        $monthlyLedgerTotal = $monthlyPaidTotal + $monthlyPendingTotal;

        if ($statusFilter !== 'all') {
            $bookings = $bookings->where('effective_payment_status', $statusFilter)->values();
        }

        return view('landlord.payments.index', compact(
            'bookings',
            'totalExpected',
            'totalPaid',
            'totalPending',
            'paidBookings',
            'pendingPayments',
            'paidCount',
            'pendingCount',
            'overdueCount',
            'statusFilter',
            'onboardingPaidTotal',
            'onboardingPendingTotal',
            'monthlyPaidTotal',
            'monthlyPendingTotal',
            'onboardingLedgerTotal',
            'monthlyLedgerTotal'
        ));
    }

    public function manageMonthly(Request $request, $bookingId)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();
        $statusFilter = $this->resolveStatusFilter($request->query('status'));

        $booking = Booking::with([
                'room.property',
                'student',
                'tenantOnboarding',
                'tenantPayments',
                'latestSubmittedTenantPayment',
                'latestTenantPayment',
            ])
            ->where('id', $bookingId)
            ->where('status', 'approved')
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->firstOrFail();

        $today = now();
        $effectiveStatus = $booking->derivedPaymentStatus($today);
        $effectiveDueDate = $booking->resolvePaymentDueDate();

        $booking->effective_payment_status = $effectiveStatus;
        $booking->effective_due_date = $effectiveDueDate;
        $booking->billing_amount = $this->resolveBookingBillingAmount($booking);
        $booking->latest_tenant_payment = $booking->latestSubmittedTenantPayment ?: $booking->latestTenantPayment;

        return view('landlord.payments.manage', compact('booking', 'statusFilter'));
    }

    public function markAsPaid($bookingId)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        $booking = Booking::where('id', $bookingId)
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->firstOrFail();

        $baseDueDate = $booking->resolvePaymentDueDate() ?: now()->startOfDay();

        $booking->update([
            'payment_status' => 'paid',
            'payment_date' => now(),
            'next_payment_due_date' => $baseDueDate->copy()->addMonthNoOverflow()->toDateString(),
            'last_overdue_notified_at' => null,
        ]);

        $this->approveLatestSubmittedTenantPayment($booking, $landlordId, $baseDueDate->toDateString());

        return back()->with('success', 'Payment marked as received.');
    }

    public function markAsPending($bookingId)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        $booking = Booking::where('id', $bookingId)
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->firstOrFail();

        $booking->payment_status = 'pending';
        $booking->payment_date = null;
        if (empty($booking->next_payment_due_date) && $booking->check_in) {
            $booking->next_payment_due_date = $booking->check_in->toDateString();
        }
        $booking->save();

        $this->reopenLatestApprovedTenantPayment($booking, $landlordId);

        return back()->with('success', 'Undo paid applied. Payment status set to pending.');
    }

    public function sendReminder($bookingId)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        $booking = Booking::with(['room.property.landlord.landlordProfile', 'student'])
            ->where('id', $bookingId)
            ->where('status', 'approved')
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->firstOrFail();

        $landlord = $booking->room->property->landlord;
        $landlordProfile = $landlord?->landlordProfile;
        $dueDate = optional($booking->resolvePaymentDueDate())->format('M d, Y') ?? 'as soon as possible';

        $qrPayload = $this->buildGcashQrPayload($landlordProfile, $booking);
        $qrUrl = $qrPayload === null ? null : $this->buildGcashQrUrl($qrPayload);

        $gcashSummary = null;
        if (filled($landlordProfile?->payment_gcash_name) && filled($landlordProfile?->payment_gcash_number)) {
            $gcashSummary = trim(sprintf(
                'GCash: %s (%s)',
                (string) $landlordProfile->payment_gcash_name,
                (string) $landlordProfile->payment_gcash_number
            ));
        }

        try {
            $booking->student->notify(new SystemNotification(
                'Payment reminder',
                sprintf(
                    'Payment reminder for Room %s at %s. Please settle your dues by %s.',
                    $booking->room->room_number,
                    $booking->room->property->name,
                    $dueDate
                ),
                route('student.bookings.index'),
                [
                    'booking_id' => $booking->id,
                    'due_date' => $dueDate,
                    'gcash_summary' => $gcashSummary,
                    'gcash_qr_url' => $qrUrl,
                ]
            ));
        } catch (\Throwable $e) {
            // Ignore notification storage errors.
        }

        try {
            $emailLines = [
                sprintf('Payment reminder for %s - Room %s.', $booking->room->property->name, $booking->room->room_number),
                sprintf('Due date: %s', $dueDate),
            ];

            if ($gcashSummary) {
                $emailLines[] = $gcashSummary;
            }
            if ($qrUrl) {
                $emailLines[] = 'GCash QR: ' . $qrUrl;
            }

            $emailLines[] = 'Thank you.';

            Mail::raw(implode("\n", $emailLines), function ($message) use ($booking) {
                $message->to($booking->student->email)->subject('Payment Reminder');
            });
        } catch (\Throwable $e) {
            // Ignore email transport errors for now.
        }

        try {
            $landlord?->notify(new SystemNotification(
                'Payment reminder sent',
                sprintf('Reminder sent to %s for Room %s.', $booking->student->full_name, $booking->room->room_number),
                route('landlord.payments.index', ['status' => 'overdue']),
                ['booking_id' => $booking->id]
            ));
        } catch (\Throwable $e) {
            // Ignore notification storage errors.
        }

        return back()->with('success', 'Payment reminder sent to tenant.');
    }

    public function storeMonthlyRecord(Request $request, $bookingId)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        $booking = Booking::with(['room.property', 'student'])
            ->where('id', $bookingId)
            ->where('status', 'approved')
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->firstOrFail();

        $validated = $request->validate([
            'manual_booking_id' => 'required|integer',
            'billing_month' => 'required|date_format:Y-m',
            'due_date' => 'nullable|date',
            'amount_due' => 'required|numeric|min:0.01|max:9999999.99',
            'payment_method' => 'nullable|string|in:cash,bank,gcash',
            'payment_reference' => 'nullable|string|max:120',
            'status' => 'required|string|in:submitted,approved,rejected',
            'payment_notes' => 'nullable|string|max:1000',
            'status_context' => 'nullable|string|in:all,paid,pending,overdue',
        ], [
            'billing_month.required' => 'Please choose the billing month.',
            'billing_month.date_format' => 'Billing month must be in YYYY-MM format.',
            'amount_due.required' => 'Please enter the amount due.',
            'amount_due.numeric' => 'Amount due must be a valid number.',
            'status.required' => 'Please choose the payment status.',
            'status.in' => 'Selected payment status is invalid.',
        ]);

        if ((int) $validated['manual_booking_id'] !== (int) $booking->id) {
            return back()->withErrors(['error' => 'Booking context mismatch for manual payment record.'])->withInput();
        }

        $billingForDate = Carbon::createFromFormat('Y-m', (string) $validated['billing_month'])->startOfMonth();
        $dueDate = !empty($validated['due_date'])
            ? Carbon::parse((string) $validated['due_date'])->startOfDay()
            : ($booking->resolvePaymentDueDate() ?: $billingForDate->copy());
        $status = strtolower((string) $validated['status']);

        TenantPayment::create([
            'booking_id' => $booking->id,
            'student_id' => $booking->student_id,
            'billing_for_date' => $billingForDate->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'amount_due' => (float) $validated['amount_due'],
            'payment_method' => !empty($validated['payment_method']) ? strtolower((string) $validated['payment_method']) : null,
            'payment_reference' => filled($validated['payment_reference'] ?? null)
                ? trim((string) $validated['payment_reference'])
                : null,
            'payment_proof_path' => null,
            'payment_notes' => filled($validated['payment_notes'] ?? null)
                ? trim((string) $validated['payment_notes'])
                : null,
            'status' => $status,
            'submitted_at' => now(),
            'reviewed_at' => in_array($status, ['approved', 'rejected'], true) ? now() : null,
            'reviewed_by' => in_array($status, ['approved', 'rejected'], true) ? $landlordId : null,
            'review_notes' => null,
        ]);

        $currentDueDate = $booking->resolvePaymentDueDate();
        $isCurrentCycleRecord = $currentDueDate
            && $currentDueDate->copy()->startOfDay()->equalTo($billingForDate);

        if ($isCurrentCycleRecord) {
            if ($status === 'approved') {
                $booking->update([
                    'payment_status' => 'paid',
                    'payment_date' => now(),
                    'next_payment_due_date' => $currentDueDate->copy()->addMonthNoOverflow()->toDateString(),
                    'last_overdue_notified_at' => null,
                ]);
            }

            if ($status === 'submitted') {
                $booking->update([
                    'payment_status' => 'pending',
                    'payment_date' => null,
                    'last_overdue_notified_at' => null,
                ]);
            }
        }

        $statusContext = $validated['status_context'] ?? $this->resolveStatusFilter($request->query('status'));

        return redirect()
            ->route('landlord.payments.manage', ['booking' => $booking->id, 'status' => $statusContext])
            ->with('success', 'Monthly payment record created successfully.');
    }

    public function updateMonthlyRecordStatus(Request $request, $bookingId, $recordId)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        $booking = Booking::query()
            ->where('id', $bookingId)
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->firstOrFail();

        $record = TenantPayment::query()
            ->where('id', $recordId)
            ->where('booking_id', $booking->id)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|string|in:submitted,approved,rejected',
            'status_context' => 'nullable|string|in:all,paid,pending,overdue',
        ], [
            'status.required' => 'Please choose a record status.',
            'status.in' => 'Selected record status is invalid.',
        ]);

        $targetStatus = strtolower((string) $validated['status']);

        $record->update([
            'status' => $targetStatus,
            'reviewed_at' => in_array($targetStatus, ['approved', 'rejected'], true) ? now() : null,
            'reviewed_by' => in_array($targetStatus, ['approved', 'rejected'], true) ? $landlordId : null,
            'review_notes' => null,
        ]);

        $currentDueDate = $booking->resolvePaymentDueDate();
        $recordBillingDate = !empty($record->billing_for_date)
            ? Carbon::parse((string) $record->billing_for_date)->startOfDay()
            : null;
        $isCurrentCycleRecord = $currentDueDate
            && $recordBillingDate
            && $currentDueDate->copy()->startOfDay()->equalTo($recordBillingDate);

        if ($isCurrentCycleRecord) {
            if ($targetStatus === 'approved') {
                $booking->update([
                    'payment_status' => 'paid',
                    'payment_date' => now(),
                    'next_payment_due_date' => $currentDueDate->copy()->addMonthNoOverflow()->toDateString(),
                    'last_overdue_notified_at' => null,
                ]);
            }

            if (in_array($targetStatus, ['submitted', 'rejected'], true)) {
                $booking->update([
                    'payment_status' => 'pending',
                    'payment_date' => null,
                    'last_overdue_notified_at' => null,
                ]);
            }
        }

        $statusContext = $validated['status_context'] ?? $this->resolveStatusFilter($request->query('status'));

        return redirect()
            ->route('landlord.payments.manage', ['booking' => $booking->id, 'status' => $statusContext])
            ->with('success', 'Payment record status updated.');
    }

    private function buildGcashQrPayload(?LandlordProfile $landlordProfile, Booking $booking): ?string
    {
        if (!$landlordProfile) {
            return null;
        }

        if (!filled($landlordProfile->payment_gcash_name) || !filled($landlordProfile->payment_gcash_number)) {
            return null;
        }

        return trim(sprintf(
            "GCash payment to %s (%s) for Room %s - %s",
            (string) $landlordProfile->payment_gcash_name,
            (string) $landlordProfile->payment_gcash_number,
            (string) $booking->room->room_number,
            (string) ($booking->student->full_name ?? 'Tenant')
        ));
    }

    private function resolveBookingBillingAmount(Booking $booking): float
    {
        $onboardingAmount = optional($booking->tenantOnboarding)->deposit_amount;
        if (is_numeric($onboardingAmount) && (float) $onboardingAmount > 0) {
            return (float) $onboardingAmount;
        }

        $monthlyRent = is_numeric($booking->monthly_rent_amount) && (float) $booking->monthly_rent_amount > 0
            ? (float) $booking->monthly_rent_amount
            : (float) ($booking->room->price ?? 0);
        $advanceAmount = !empty($booking->include_advance_payment) ? $monthlyRent : 0.0;

        return $monthlyRent + $advanceAmount;
    }

    private function approveLatestSubmittedTenantPayment(Booking $booking, int $landlordId, string $billingDate): void
    {
        $record = TenantPayment::query()
            ->where('booking_id', $booking->id)
            ->where('status', 'submitted')
            ->whereDate('billing_for_date', $billingDate)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            $record = TenantPayment::query()
                ->where('booking_id', $booking->id)
                ->where('status', 'submitted')
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->first();
        }

        if (!$record) {
            return;
        }

        $record->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $landlordId,
            'review_notes' => null,
        ]);
    }

    private function reopenLatestApprovedTenantPayment(Booking $booking, int $landlordId): void
    {
        $record = TenantPayment::query()
            ->where('booking_id', $booking->id)
            ->where('status', 'approved')
            ->where('reviewed_by', $landlordId)
            ->orderByDesc('reviewed_at')
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            return;
        }

        $record->update([
            'status' => 'submitted',
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_notes' => null,
        ]);
    }

    private function buildGcashQrUrl(string $payload): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . rawurlencode($payload);
    }
}
