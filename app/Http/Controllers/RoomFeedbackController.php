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

        // Must have had an approved booking for this room
        $hasBooking = $room->bookings()
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->exists();

        if (!$hasBooking) {
            return back()->withErrors(['feedback' => 'Only students who have stayed in this room can leave feedback.']);
        }

        // One feedback per student per room
        if (RoomFeedback::where('room_id', $room->id)->where('user_id', $student->id)->exists()) {
            return back()->withErrors(['feedback' => 'You have already submitted feedback for this room.']);
        }

        $validated = $request->validate([
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'required|string|max:1000',
            'display_name' => 'nullable|string|max:80',
            'anonymous'    => 'nullable|boolean',
        ]);

        $sentimentLabel = null;
        $sentimentScore = null;

        $token = config('services.huggingface.token');
        if (!empty($token)) {
            try {
                $response = Http::withToken($token)
                    ->acceptJson()
                    ->timeout(10)
                    ->post('https://router.huggingface.co/hf-inference/models/tabularisai/multilingual-sentiment-analysis', [
                        'inputs' => $validated['comment'],
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    $candidates = [];
                    if (is_array($data) && isset($data[0]) && is_array($data[0]) && isset($data[0]['label'])) {
                        $candidates = $data;
                    } elseif (is_array($data) && isset($data[0]) && is_array($data[0]) && isset($data[0][0]['label'])) {
                        $candidates = $data[0];
                    }

                    if (!empty($candidates)) {
                        usort($candidates, fn($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));
                        $best = $candidates[0] ?? null;

                        if (!empty($best['label'])) {
                            $label = strtolower((string) $best['label']);
                            if (str_contains($label, 'pos')) {
                                $sentimentLabel = 'positive';
                            } elseif (str_contains($label, 'neg')) {
                                $sentimentLabel = 'negative';
                            } elseif (str_contains($label, 'neu')) {
                                $sentimentLabel = 'neutral';
                            } else {
                                $sentimentLabel = $label;
                            }
                            $sentimentScore = isset($best['score']) ? (float) $best['score'] : null;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Skip sentiment if provider is unavailable.
            }
        }

        RoomFeedback::create([
            'room_id'      => $room->id,
            'user_id'      => $student->id,
            'rating'       => $validated['rating'],
            'comment'      => $validated['comment'],
            'display_name' => $request->boolean('anonymous') ? null : ($validated['display_name'] ?: $student->full_name),
            'sentiment_label' => $sentimentLabel,
            'sentiment_score' => $sentimentScore,
        ]);

        return redirect()
            ->route('student.rooms.show', $room->id)
            ->with('success', 'Thank you! Your feedback has been submitted.');
    }
}
