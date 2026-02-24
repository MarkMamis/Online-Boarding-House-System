<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\LeaveRequest;
use App\Models\TenantOnboarding;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StudentLeaveRequestController extends Controller
{
    private function redirectBackToPanel(Request $request)
    {
        $panel = trim((string) $request->input('panel', ''));
        if ($panel === '') {
            return back();
        }

        $previous = (string) url()->previous();
        $previous = preg_replace('/#.*/', '', $previous) ?: $previous;

        return redirect()->to($previous . '#' . $panel);
    }

    protected function ensureStudent()
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403, 'Unauthorized');
        }
    }

    public function store(Request $request)
    {
        $this->ensureStudent();

        $studentId = Auth::id();
        $today = now()->toDateString();

        $validator = Validator::make($request->all(), [
            'leave_date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:1000',
            'panel' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->redirectBackToPanel($request)
                ->withErrors($validator, 'leave_request')
                ->withInput();
        }

        // Must have a current approved booking (not ended)
        $booking = Booking::query()
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->where('check_out', '>', $today)
            ->with(['room.property'])
            ->orderByDesc('check_in')
            ->first();

        if (!$booking || !$booking->room || !$booking->room->property) {
            return $this->redirectBackToPanel($request)
                ->with('error', 'You can request leave only if you have an approved booking.');
        }

        // Must have an onboarding record for this booking (any status)
        $hasOnboarding = TenantOnboarding::query()
            ->where('booking_id', $booking->id)
            ->exists();

        if (!$hasOnboarding) {
            return $this->redirectBackToPanel($request)
                ->with('error', 'Onboarding is required before requesting leave.');
        }

        $leaveDate = (string) $validator->validated()['leave_date'];
        if (!empty($booking->check_out) && $leaveDate >= (string) $booking->check_out->toDateString()) {
            return $this->redirectBackToPanel($request)
                ->withErrors(['leave_date' => 'Leave date must be before your check-out date.'], 'leave_request')
                ->withInput();
        }

        $landlordId = (int) ($booking->room->property->landlord_id ?? 0);
        if ($landlordId <= 0) {
            return $this->redirectBackToPanel($request)
                ->with('error', 'Unable to find landlord for your booking.');
        }

        // Prevent spamming: one pending request per booking
        $alreadyPending = LeaveRequest::query()
            ->where('booking_id', $booking->id)
            ->where('student_id', $studentId)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return $this->redirectBackToPanel($request)
                ->with('error', 'You already have a pending leave request.');
        }

        $leaveRequest = LeaveRequest::create([
            'booking_id' => $booking->id,
            'student_id' => $studentId,
            'landlord_id' => $landlordId,
            'status' => 'pending',
            'leave_date' => $leaveDate,
            'reason' => $validator->validated()['reason'] ?? null,
        ]);

        try {
            $landlord = $booking->room->property->landlord;
            if ($landlord) {
                $landlord->notify(new SystemNotification(
                    'Leave request submitted',
                    sprintf(
                        '%s requested leave on %s for %s (Room %s).',
                        Auth::user()->full_name,
                        $leaveRequest->leave_date->format('M d, Y'),
                        $booking->room->property->name ?? 'Property',
                        $booking->room->room_number ?? $booking->room_id
                    ),
                    route('landlord.leave_requests.index'),
                    ['leave_request_id' => $leaveRequest->id, 'booking_id' => $booking->id]
                ));
            }
        } catch (\Throwable $e) {
            // ignore notification errors
        }

        return $this->redirectBackToPanel($request)
            ->with('success', 'Leave request submitted.');
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest)
    {
        $this->ensureStudent();

        if ((int) $leaveRequest->student_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($leaveRequest->status !== 'pending') {
            return $this->redirectBackToPanel($request)->with('error', 'Only pending leave requests can be cancelled.');
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return $this->redirectBackToPanel($request)->with('success', 'Leave request cancelled.');
    }
}
