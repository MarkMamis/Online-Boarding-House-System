<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
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

    private function bookedLandlordIdsForStudent(User $student): array
    {
        return \App\Models\Booking::query()
            ->where('student_id', $student->id)
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->distinct()
            ->pluck('properties.landlord_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function index()
    {
        if (!Auth::check()) {
            abort(403);
        }
        $user = Auth::user();

        $baseQuery = Message::with(['sender', 'receiver', 'property'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            });

        // For landlords, prioritize newest incoming first; for students, show mix
        $messages = $baseQuery->orderByDesc('created_at')->limit(100)->get();

        // Build list of possible recipients for reply interface
        $recipients = [];
        $messageProperties = collect();
        if ($user->role === 'landlord') {
            // Students who have messaged this landlord or referenced their properties
            $recipients = Message::where('receiver_id', $user->id)
                ->with('sender')
                ->get()
                ->pluck('sender')
                ->unique('id')
                ->values();
        } elseif ($user->role === 'student') {
            // Students can only message the landlord(s) they have bookings with.
            $allowedLandlordIds = $this->bookedLandlordIdsForStudent($user);
            $recipients = empty($allowedLandlordIds)
                ? collect()
                : User::query()
                    ->whereIn('id', $allowedLandlordIds)
                    ->orderBy('full_name')
                    ->get();

            $messageProperties = \App\Models\Property::query()
                ->join('rooms', 'rooms.property_id', '=', 'properties.id')
                ->join('bookings', 'bookings.room_id', '=', 'rooms.id')
                ->where('bookings.student_id', $user->id)
                ->whereIn('bookings.status', ['pending', 'approved'])
                ->select('properties.id', 'properties.name', 'properties.landlord_id')
                ->distinct()
                ->orderBy('properties.name')
                ->get();
        }
        $messageThreads = collect();
        if ($user->role === 'student') {
            $allMessages = Message::with(['sender:id,full_name', 'receiver:id,full_name', 'property:id,name'])
                ->where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
                })
                ->latest()
                ->limit(200)
                ->get();

            $threads = [];
            foreach ($allMessages as $msg) {
                $otherId = (int) $msg->sender_id === $user->id ? $msg->receiver_id : $msg->sender_id;
                $propId = $msg->property_id ?? 0;
                $key = $otherId . '_' . $propId;
                if (!isset($threads[$key])) {
                    $threads[$key] = [
                        'other' => (int) $msg->sender_id === $user->id ? $msg->receiver : $msg->sender,
                        'property' => $msg->property,
                        'property_id' => $msg->property_id,
                        'latest' => $msg,
                        'unread' => 0,
                        'messages' => [],
                    ];
                }
                if (empty($msg->read_at) && (int) $msg->receiver_id === $user->id) {
                    $threads[$key]['unread']++;
                }
                $threads[$key]['messages'][] = $msg;
            }
            $messageThreads = collect(array_values($threads));
        }

        return $user->role === 'landlord'
            ? view('landlord.messages.index', compact('messages', 'recipients', 'user'))
            : view('messages.index', compact('messages', 'recipients', 'user', 'messageProperties', 'messageThreads'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            abort(403);
        }
        $this->authorize('create', Message::class);

        $errorBag = trim((string) $request->input('error_bag', ''));

        $isStudent = Auth::user()->role === 'student';

        $rules = [
            'body' => 'required|string|max:2000',
        ];

        if ($isStudent) {
            $rules['property_id'] = 'required|exists:properties,id';
            $rules['receiver_id'] = 'nullable|exists:users,id';
        } else {
            $rules['receiver_id'] = 'required|exists:users,id';
            $rules['property_id'] = 'nullable|exists:properties,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $redirect = $this->redirectBackToPanel($request)->withInput();
            return $errorBag !== ''
                ? $redirect->withErrors($validator, $errorBag)
                : $redirect->withErrors($validator);
        }

        $data = $validator->validated();
        $data['sender_id'] = Auth::id();

        // Students can only message the owner of a property they have booked.
        if ($isStudent) {
            $propertyId = (int) ($data['property_id'] ?? 0);

            $hasBookingForProperty = \App\Models\Booking::query()
                ->where('student_id', Auth::id())
                ->whereIn('bookings.status', ['pending', 'approved'])
                ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
                ->where('rooms.property_id', $propertyId)
                ->exists();

            if (!$hasBookingForProperty) {
                $redirect = $this->redirectBackToPanel($request)->withInput();
                $errors = ['property_id' => 'You can only message the owner of a property you booked.'];
                return $errorBag !== ''
                    ? $redirect->withErrors($errors, $errorBag)
                    : $redirect->withErrors($errors);
            }

            $prop = Property::find($propertyId);
            if (!$prop) {
                $redirect = $this->redirectBackToPanel($request)->withInput();
                $errors = ['property_id' => 'Invalid property.'];
                return $errorBag !== ''
                    ? $redirect->withErrors($errors, $errorBag)
                    : $redirect->withErrors($errors);
            }

            $data['receiver_id'] = (int) $prop->landlord_id;
        }

        // Students/landlords can only message landlord accounts when receiver is provided.
        if (!empty($data['receiver_id']) && Auth::user()->role === 'student') {
            $receiver = User::find($data['receiver_id']);
            $receiverRole = $receiver ? strtolower(trim((string) $receiver->role)) : '';
            if (!$receiver || $receiverRole !== 'landlord') {
                $redirect = $this->redirectBackToPanel($request)->withInput();
                $errors = ['receiver_id' => 'You can only message landlord accounts.'];
                return $errorBag !== ''
                    ? $redirect->withErrors($errors, $errorBag)
                    : $redirect->withErrors($errors);
            }
        }

        // If property supplied (non-student flows), enforce receiver is landlord of that property
        if (!$isStudent && !empty($data['property_id'])) {
            $prop = Property::find($data['property_id']);
            if ($prop && $prop->landlord_id !== (int) $data['receiver_id']) {
                $data['property_id'] = null;
            }
        }

        // Prevent sending messages to self
        if ((int)$data['receiver_id'] === Auth::id()) {
            $redirect = $this->redirectBackToPanel($request)->withInput();
            $errors = ['receiver_id' => 'Cannot send a message to yourself.'];
            return $errorBag !== ''
                ? $redirect->withErrors($errors, $errorBag)
                : $redirect->withErrors($errors);
        }

        Message::create($data);

        return $this->redirectBackToPanel($request)->with('success', 'Message sent.');
    }

    public function read(Message $message)
    {
        if (!Auth::check()) { abort(403); }
        $this->authorize('markRead', $message);
        if ($message->read_at === null) {
            $message->read_at = now();
            $message->save();
        }
        return back()->with('success', 'Message marked as read.');
    }

    /**
     * Inquiry from a student to a room's landlord — no prior booking required.
     */
    public function storeInquiry(Request $request, \App\Models\Room $room)
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }

        $room->load('property:id,name,address,landlord_id,approval_status');

        if (($room->property->approval_status ?? 'pending') !== 'approved') {
            abort(404);
        }

        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $landlordId = (int) $room->property->landlord_id;

        if ($landlordId === Auth::id()) {
            return back()->withErrors(['body' => 'Cannot send a message to yourself.']);
        }

        Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $landlordId,
            'property_id' => $room->property_id,
            'body'        => $request->input('body'),
        ]);

        return redirect()
            ->route('student.rooms.show', $room->id)
            ->with('success', 'Your message was sent to the landlord.');
    }
}
