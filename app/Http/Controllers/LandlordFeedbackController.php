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
        $token = config('services.huggingface.token');
        if (empty($token)) {
            return back()->with('status', 'HF_TOKEN is not configured.');
        }

        $baseQuery = RoomFeedback::query()
            ->whereNull('sentiment_label')
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
            ->chunkById(25, function ($batch) use ($token, &$processed) {
                foreach ($batch as $feedback) {
                    [$label, $score] = $this->analyzeComment($token, $feedback->comment);
                    if ($label) {
                        $feedback->sentiment_label = $label;
                        $feedback->sentiment_score = $score;
                        $feedback->save();
                        $processed++;
                    }
                }
            });

        return back()->with('status', "Sentiment analysis complete: {$processed} updated.");
    }

    private function analyzeComment(string $token, string $comment): array
    {
        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(10)
                ->post('https://router.huggingface.co/hf-inference/models/tabularisai/multilingual-sentiment-analysis', [
                    'inputs' => $comment,
                ]);

            if (!$response->successful()) {
                return [null, null];
            }

            $data = $response->json();
            $candidates = [];

            if (is_array($data) && isset($data[0]) && is_array($data[0]) && isset($data[0]['label'])) {
                $candidates = $data;
            } elseif (is_array($data) && isset($data[0]) && is_array($data[0]) && isset($data[0][0]['label'])) {
                $candidates = $data[0];
            }

            if (empty($candidates)) {
                return [null, null];
            }

            usort($candidates, fn($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));
            $best = $candidates[0] ?? null;
            if (empty($best['label'])) {
                return [null, null];
            }

            $label = strtolower((string) $best['label']);
            if (str_contains($label, 'pos')) {
                $label = 'positive';
            } elseif (str_contains($label, 'neg')) {
                $label = 'negative';
            } elseif (str_contains($label, 'neu')) {
                $label = 'neutral';
            }

            $score = isset($best['score']) ? (float) $best['score'] : null;

            return [$label, $score];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }
}
