<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class RoomFeedbackController extends Controller
{
    public function store(Request $request, Room $room)
    {
        $student = Auth::user();

        // Only students can submit feedback
        if ($student->role !== 'student') {
            abort(403);
        }

        // Must have completed onboarding tied to an approved booking for this room that is still valid
        $today = now()->toDateString();
        $hasBooking = $room->bookings()
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->whereDate('check_out', '>', $today)
            ->whereHas('tenantOnboarding', function ($query) {
                $query->where('status', 'completed');
            })
            ->exists();

        if (!$hasBooking) {
            return back()->withErrors(['feedback' => 'Only verified tenants can rate or leave feedback for this room.']);
        }

        // One feedback per student per room
        if (RoomFeedback::where('room_id', $room->id)->where('user_id', $student->id)->exists()) {
            return back()->withErrors(['feedback' => 'You have already submitted feedback for this room.']);
        }

        $validated = $request->validate([
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:1000',
            'display_name' => 'nullable|string|max:80',
            'anonymous'    => 'nullable|boolean',
        ]);

        $comment = trim((string) ($validated['comment'] ?? ''));
        $sentimentLabel = null;
        $sentimentScore = null;

        if ($comment !== '') {
            try {
                $sentimentLabel = app(\App\Services\GeminiService::class)->classify(
                    $comment,
                    ['positive', 'neutral', 'negative'],
                    'You are a sentiment analyzer for student accommodation feedback. Classify the user comment into exactly one label: positive, neutral, negative.'
                );
            } catch (\Throwable $e) {
                // Skip sentiment if provider is unavailable; feedback will be saved without sentiment and can be retried.
                $sentimentLabel = null;
            }
        }

        RoomFeedback::create([
            'room_id'      => $room->id,
            'user_id'      => $student->id,
            'rating'       => $validated['rating'],
            'comment'      => $comment,
            'display_name' => $request->boolean('anonymous') ? null : (($validated['display_name'] ?? null) ?: $student->full_name),
            'sentiment_label' => $sentimentLabel,
            'sentiment_score' => $sentimentScore,
        ]);

        return redirect()
            ->route('student.rooms.feedback_page', $room->id)
            ->with('success', 'Thank you! Your feedback has been submitted.');
    }
}
