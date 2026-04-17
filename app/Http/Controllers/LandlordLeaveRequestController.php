<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LandlordLeaveRequestController extends Controller
{
    protected function ensureLandlord()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->ensureLandlord();

        $leaveRequests = LeaveRequest::query()
            ->where('landlord_id', Auth::id())
            ->with(['student', 'booking.room.property'])
            ->orderByRaw("FIELD(status,'pending','approved','rejected','cancelled')")
            ->orderByDesc('created_at')
            ->get();

        return view('landlord.leave_requests.index', compact('leaveRequests'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $this->ensureLandlord();

        if ((int) $leaveRequest->landlord_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $leaveRequest->loadMissing(['booking.room']);
        if (!$leaveRequest->booking || !$leaveRequest->booking->room) {
            return back()->with('error', 'Booking not found for this leave request.');
        }

        if (($leaveRequest->booking->status ?? '') !== 'approved') {
            return back()->with('error', 'Only approved bookings can be processed for leave.');
        }

        $today = now()->toDateString();
        $leaveDate = optional($leaveRequest->leave_date)->toDateString();
        if (!$leaveDate) {
            return back()->with('error', 'Invalid leave date.');
        }

        $bookingCheckOut = optional($leaveRequest->booking->check_out)->toDateString();
        if ($bookingCheckOut && $leaveDate >= $bookingCheckOut) {
            return back()->with('error', 'Leave date must be before the booking check-out date.');
        }

        $validator = Validator::make($request->all(), [
            'landlord_response' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($leaveRequest, $validator, $leaveDate) {
            $leaveRequest->refresh();
            $leaveRequest->update([
                'status' => 'approved',
                'landlord_response' => $validator->validated()['landlord_response'] ?? null,
                'responded_at' => now(),
            ]);

            $leaveRequest->booking->update([
                'check_out' => $leaveDate,
                'status' => 'cancelled',
            ]);

            $leaveRequest->booking->room->syncAvailabilitySnapshot();
        });

        try {
            $student = $leaveRequest->student;
            if ($student) {
                $student->notify(new SystemNotification(
                    'Leave request approved',
                    'Your landlord approved your leave request.',
                    route('student.dashboard') . '#onboarding',
                    ['leave_request_id' => $leaveRequest->id]
                ));
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->ensureLandlord();

        if ((int) $leaveRequest->landlord_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $validator = Validator::make($request->all(), [
            'landlord_response' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'landlord_response' => $validator->validated()['landlord_response'] ?? null,
            'responded_at' => now(),
        ]);

        try {
            $student = $leaveRequest->student;
            if ($student) {
                $student->notify(new SystemNotification(
                    'Leave request rejected',
                    'Your landlord rejected your leave request.',
                    route('student.dashboard') . '#onboarding',
                    ['leave_request_id' => $leaveRequest->id]
                ));
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return back()->with('success', 'Leave request rejected.');
    }
}
