<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\Message;
use App\Models\TenantOnboarding;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingController extends Controller
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

    protected function ensureLandlord()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403, 'Unauthorized');
        }
    }

    protected function studentBookingEligibility(): array
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();
        $status = (string) ($student->school_id_verification_status ?? '');

        if ($status === '') {
            $hasVerificationDocument = filled($student->school_id_path) || filled($student->enrollment_proof_path);
            $status = $hasVerificationDocument ? 'pending' : 'not_submitted';
        }

        if ($status === 'approved') {
            return [true, null];
        }

        if ($status === 'rejected') {
            return [
                false,
                'Your academic verification document was rejected. Upload a valid School ID or COR/COE in Student Setup before booking.',
            ];
        }

        if ($status === 'not_submitted') {
            return [
                false,
                'Booking is locked until you upload your School ID or COR/COE and complete Student Setup. You can still browse rooms and properties.',
            ];
        }

        return [
            false,
            'Booking is locked while your academic verification is pending admin approval. You can still browse rooms and properties.',
        ];
    }

    // Student: browse available rooms
    public function browse()
    {
        $this->ensureStudent();
        $rooms = Room::with('property.landlord')
            ->where('status', 'available')
            ->where('slots_available', '>', 0)
            ->whereHas('property', function ($q) {
                $q->where('approval_status', 'approved');
            })
            ->orderBy('price')
            ->get();
        return view('student.rooms.index', compact('rooms'));
    }

    // Student: view own bookings
    public function studentIndex()
    {
        $this->ensureStudent();
        $bookings = Booking::with(['room.property'])
            ->where('student_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
        return view('student.bookings.index', compact('bookings'));
    }

    // Student: show booking form for a room
    public function create(Room $room)
    {
        $this->ensureStudent();
        $this->authorize('create', Booking::class);
        $room->load(['property.landlord.landlordProfile', 'roomImages']);

        [$canBook, $bookingBlockMessage] = $this->studentBookingEligibility();
        if (!$canBook) {
            return redirect()->route('student.rooms.show', $room->id)->with('error', $bookingBlockMessage);
        }

        $today = now()->toDateString();
        
        // Check for any active booking (pending or approved)
        $existingBooking = Booking::query()
            ->where('student_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->where('check_out', '>', $today)
            ->first();
        
        if ($existingBooking) {
            // Allow if it's the same room
            if ($existingBooking->room_id !== $room->id) {
                return redirect()->route('student.dashboard')
                    ->with('error', 'You already have an active booking. Complete or cancel it to book another room.');
            }
        }

        if (($room->property->approval_status ?? 'pending') !== 'approved') {
            return redirect()->route('student.rooms.index')->with('error', 'This property is not available yet.');
        }
        
        // Check if room is in maintenance
        if ($room->status === 'maintenance') {
            return redirect()->route('student.rooms.index')->with('error', 'Room is currently under maintenance.');
        }
        
        // Check if room has available slots (based on actual occupancy, not manual status)
        if (!$room->hasAvailableSlots()) {
            return redirect()->route('student.rooms.index')->with('error', 'Room is at full capacity.');
        }

        $student    = Auth::user();
        $landlord   = $room->property->landlord ?? null;
        $coverImage = $room->roomImages->firstWhere('is_cover', true) ?? $room->roomImages->first();

        return view('student.bookings.create', compact('room', 'student', 'landlord', 'coverImage'));
    }

    // Student: store booking
    public function store(Request $request, Room $room)
    {
        $this->ensureStudent();
        $this->authorize('create', Booking::class);
        $room->load('property.landlord');

        $stayOnPage = $request->boolean('stay');

        [$canBook, $bookingBlockMessage] = $this->studentBookingEligibility();
        if (!$canBook) {
            if ($stayOnPage) {
                return back()->with('error', $bookingBlockMessage);
            }

            return redirect()->route('student.rooms.index')->with('error', $bookingBlockMessage);
        }

        $today = now()->toDateString();
        $hasCurrentApprovedBooking = Booking::query()
            ->where('student_id', Auth::id())
            ->where('status', 'approved')
            ->where('check_out', '>', $today)
            ->exists();
        if ($hasCurrentApprovedBooking) {
            if ($stayOnPage) {
                return back()->with('error', 'You already have an approved booking. Booking is disabled while your stay is active.');
            }
            return redirect()->route('student.dashboard')
                ->with('error', 'You already have an approved booking. Booking is disabled while your stay is active.');
        }

        if (($room->property->approval_status ?? 'pending') !== 'approved') {
            if ($stayOnPage) {
                return back()->with('error', 'This property is not available yet.');
            }
            return redirect()->route('student.rooms.index')->with('error', 'This property is not available yet.');
        }
        
        // Check if room is in maintenance
        if ($room->status === 'maintenance') {
            if ($stayOnPage) {
                return back()->with('error', 'Room is currently under maintenance.');
            }
            return redirect()->route('student.rooms.index')->with('error', 'Room is currently under maintenance.');
        }
        
        // Check if room has available slots (based on actual occupancy, not manual status)
        if (!$room->hasAvailableSlots()) {
            if ($stayOnPage) {
                return back()->with('error', 'Room is at full capacity.');
            }
            return redirect()->route('student.rooms.index')->with('error', 'Room is at full capacity.');
        }

        $validator = Validator::make($request->all(), [
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'notes' => 'nullable|string|max:1000',
            'include_advance_payment' => 'nullable|boolean',
            'occupancy_mode' => 'required|in:solo,shared',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'booking')
                ->withInput()
                ->with('booking_form_action', route('bookings.store', $room->id))
                ->with('booking_room_label', sprintf('%s — Room %s', $room->property->name ?? 'Property', $room->room_number ?? $room->id));
        }

        $data = $validator->validated();
        $data['room_id'] = $room->id;
        $data['student_id'] = Auth::id();
        $data['status'] = 'pending';

        $roomRequiresAdvance = Schema::hasColumn('rooms', 'requires_advance_payment')
            && (bool) ($room->requires_advance_payment ?? false);

        $data['include_advance_payment'] = $roomRequiresAdvance
            ? true
            : $request->boolean('include_advance_payment', true);

        $occupancyMode = (string) $request->input('occupancy_mode', 'solo');
        $roomCapacity = max(1, (int) $room->capacity);
        $monthlyRentAmount = $occupancyMode === 'shared'
            ? round(((float) $room->price) / $roomCapacity, 2)
            : (float) $room->price;

        $data['occupancy_mode'] = $occupancyMode;
        $data['monthly_rent_amount'] = $monthlyRentAmount;

        $booking = Booking::create($data);

        // Notify landlord automatically via message and email
        $landlord = $room->property->landlord;

        try {
            $landlord->notify(new SystemNotification(
                'New booking request',
                sprintf(
                    'Room %s at %s (%s) requested by %s (%s to %s).',
                    $room->room_number,
                    $room->property->name,
                    $room->property->address,
                    Auth::user()->full_name,
                    $booking->check_in->format('M d, Y'),
                    $booking->check_out->format('M d, Y')
                ),
                route('landlord.bookings.index'),
                ['booking_id' => $booking->id, 'room_id' => $room->id, 'property_id' => $room->property_id]
            ));
        } catch (\Throwable $e) {
            // ignore notifications storage errors
        }

        try {
            Mail::raw(
                sprintf(
                    "New booking request for Room %s at %s (%s) from %s, %s to %s.",
                    $room->room_number,
                    $room->property->name,
                    $room->property->address,
                    Auth::user()->full_name,
                    $booking->check_in->format('M d, Y'),
                    $booking->check_out->format('M d, Y')
                ),
                function ($message) use ($landlord) {
                    $message->to($landlord->email)->subject('New Booking Request');
                }
            );
        } catch (\Throwable $e) {
            // ignore email transport errors for now
        }

        if ($stayOnPage) {
            return redirect()->to(url()->previous())->with('booking_success', 'Booking request submitted.');
        }

        return redirect()->route('student.bookings.index')->with('success', 'Booking request submitted.');
    }

    // Student: cancel own pending booking
    public function cancel(Booking $booking)
    {
        $this->ensureStudent();
        $this->authorize('cancel', $booking);

        $request = request();
        $stayOnPage = $request->boolean('stay');

        if ($booking->status !== 'pending') {
            return $this->redirectBackToPanel($request)->with('error', 'Only pending bookings can be cancelled.');
        }

        $cancelReason = request()->input('cancel_reason');
        $booking->update(['status' => 'cancelled', 'cancel_reason' => $cancelReason]);

        $redirect = $this->redirectBackToPanel($request);
        return $stayOnPage
            ? $redirect->with('booking_success', 'Booking cancelled.')
            : $redirect->with('success', 'Booking cancelled.');
    }

    // Landlord: list bookings across their properties
    public function landlordIndex()
    {
        $this->ensureLandlord();
        // If landlord has no properties yet, redirect to create property
        if (!Auth::user()->properties()->exists()) {
            return redirect()->route('landlord.properties.create')
                ->with('info', 'Add a property first to receive booking requests.');
        }
        $bookings = Booking::with(['room.property', 'student'])
            ->whereHas('room.property', function ($q) {
                $q->where('landlord_id', Auth::id());
            })
            ->orderByDesc('created_at')
            ->get();
        return view('landlord.bookings.index', compact('bookings'));
    }

    public function landlordTenants()
    {
        $this->ensureLandlord();

        // Get all approved bookings (current tenants) for this landlord
        $tenants = Booking::with(['room.property', 'student'])
            ->where('status', 'approved')
            ->whereHas('room.property', function ($q) {
                $q->where('landlord_id', Auth::id());
            })
            ->orderBy('check_in')
            ->get();

        return view('landlord.tenants.index', compact('tenants'));
    }

    // Landlord: approve booking
    public function approve(Booking $booking)
    {
        $this->ensureLandlord();
        $this->authorize('approve', $booking);
        DB::transaction(function () use ($booking) {
            $booking->refresh();
            $room = Room::lockForUpdate()->findOrFail($booking->room_id);

            if ((string) $booking->status !== 'pending') {
                abort(422, 'Booking is no longer pending.');
            }

            if ((int) $room->slots_available <= 0) {
                abort(422, 'No available slots left for this room.');
            }

            $booking->update([
                'status' => 'approved',
                'payment_status' => $booking->payment_status ?: 'pending',
                'next_payment_due_date' => $booking->next_payment_due_date ?: optional($booking->check_in)->toDateString(),
                'last_overdue_notified_at' => null,
            ]);

            $remainingSlots = max(0, (int) $room->slots_available - 1);
            $room->update([
                'slots_available' => $remainingSlots,
                'status' => $remainingSlots > 0 ? 'available' : 'occupied',
            ]);
        });

        // Reject overlapping pending bookings for the same room
        Booking::where('room_id', $booking->room_id)
            ->where('status', 'pending')
            ->where(function ($q) use ($booking) {
                $q->where('check_in', '<', $booking->check_out)
                  ->where('check_out', '>', $booking->check_in);
            })
            ->update(['status' => 'rejected']);

        // Email student about approval
        try {
            $booking->student->notify(new SystemNotification(
                'Booking approved',
                sprintf(
                    'Approved: Room %s at %s (%s to %s). Complete your tenant onboarding.',
                    $booking->room->room_number,
                    $booking->room->property->name,
                    $booking->check_in->format('M d, Y'),
                    $booking->check_out->format('M d, Y')
                ),
                route('student.onboarding.index'),
                ['booking_id' => $booking->id]
            ));
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            Mail::raw(
                sprintf(
                    "Your booking was approved for Room %s at %s, %s to %s. Please complete your tenant onboarding process.",
                    $booking->room->room_number,
                    $booking->room->property->name,
                    $booking->check_in->format('M d, Y'),
                    $booking->check_out->format('M d, Y')
                ),
                function ($message) use ($booking) {
                    $message->to($booking->student->email)->subject('Booking Approved - Complete Onboarding');
                }
            );
        } catch (\Throwable $e) {
            // ignore email transport errors
        }

        // Create tenant onboarding record
        TenantOnboarding::create([
            'booking_id' => $booking->id,
            'required_documents' => ['student_id', 'proof_of_income', 'emergency_contact'],
            'deposit_amount' => $booking->room->price, // full payment
        ]);

        return back()->with('success', 'Booking approved. Tenant onboarding process initiated.');
    }

    // Landlord: reject booking
    public function reject(Booking $booking)
    {
        $this->ensureLandlord();
        $this->authorize('reject', $booking);
        $booking->update(['status' => 'rejected']);
        // Email student about rejection
        try {
            $booking->student->notify(new SystemNotification(
                'Booking rejected',
                sprintf(
                    'Rejected: Room %s at %s (%s to %s).',
                    $booking->room->room_number,
                    $booking->room->property->name,
                    $booking->check_in->format('M d, Y'),
                    $booking->check_out->format('M d, Y')
                ),
                route('student.bookings.index'),
                ['booking_id' => $booking->id]
            ));
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            Mail::raw(
                sprintf(
                    "Your booking was rejected for Room %s at %s, %s to %s.",
                    $booking->room->room_number,
                    $booking->room->property->name,
                    $booking->check_in->format('M d, Y'),
                    $booking->check_out->format('M d, Y')
                ),
                function ($message) use ($booking) {
                    $message->to($booking->student->email)->subject('Booking Rejected');
                }
            );
        } catch (\Throwable $e) {
            // ignore email transport errors
        }
        return back()->with('success', 'Booking rejected.');
    }
}
