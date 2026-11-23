<?php

namespace App\Http\Controllers;

use App\Models\RoomFeedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LandlordFeedbackController extends Controller
{
    public function index()
    {
        $baseQuery = RoomFeedback::query()
            ->whereHas('room.property', function ($q) {
                $q->where('landlord_id', Auth::id());
            });

        $feedbacks = (clone $baseQuery)
            ->with(['room.property', 'user'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $avgRating = (clone $baseQuery)->avg('rating');
        $totalFeedback = (clone $baseQuery)->count();

        return view('landlord.feedback.index', compact('feedbacks', 'avgRating', 'totalFeedback'));
    }

    public function analyzePending()
    {
        $gemini = app(\App\Services\GeminiService::class);
        if (!$gemini->isConfigured()) {
            return back()->with('status', 'Gemini API key is not configured.');
        }

        $baseQuery = RoomFeedback::query()
            ->whereNull('sentiment_label')
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->whereHas('room.property', function ($q) {
                $q->where('landlord_id', Auth::id());
            });

        $pendingCount = (clone $baseQuery)->count();
        if ($pendingCount === 0) {
            return back()->with('status', 'No feedback pending analysis.');
        }

        $processed = 0;

        (clone $baseQuery)
            ->orderBy('created_at')
            ->chunkById(25, function ($batch) use ($gemini, &$processed) {
                foreach ($batch as $feedback) {
                    $comment = trim((string) $feedback->comment);
                    if ($comment === '') {
                        continue;
                    }

                    try {
                        $label = $gemini->classify(
                            $comment,
                            ['positive', 'neutral', 'negative'],
                            'You are a sentiment analyzer for student accommodation feedback. Classify the user comment into exactly one label: positive, neutral, negative.'
                        );

                        if ($label !== null) {
                            $feedback->sentiment_label = $label;
                            $feedback->sentiment_score = null;
                            $feedback->save();
                            $processed++;
                        }
                    } catch (\Throwable $e) {
                        // Leave sentiment_label null so it can be retried on next run
                    }
                }
            });

        return back()->with('status', "Sentiment analysis complete: {$processed} updated.");
    }
}
