<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ReportPriorityClassifier
{
    public function classify(string $title, string $description): string
    {
        $content = trim($title . "\n\n" . $description);
        if ($content === '') {
            return 'medium';
        }

        $gemini = app(GeminiService::class);
        if (!$gemini->isConfigured()) {
            return $this->heuristicClassify($content);
        }

        try {
            $classified = $gemini->classify(
                $content,
                ['low', 'medium', 'high'],
                "You are a severity triage classifier for student accommodation reports. "
                . "Classify each report into exactly one label: low, medium, high. "
                . "Use high for immediate safety/security risks or urgent hazards; "
                . "medium for important service disruptions; low for minor/non-urgent concerns."
            );

            if ($classified !== null) {
                return $classified;
            }
        } catch (\Throwable $e) {
            // Fall through to heuristic classifier.
        }

        return $this->heuristicClassify($content);
    }

    private function normalizePriority(?string $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $clean = strtolower(trim($value));
        $clean = preg_replace('/[^a-z]/', '', $clean) ?: '';

        if (in_array($clean, ['low', 'medium', 'high'], true)) {
            return $clean;
        }

        if (str_contains($clean, 'critical') || str_contains($clean, 'urgent') || str_contains($clean, 'severe') || str_contains($clean, 'high')) {
            return 'high';
        }

        if (str_contains($clean, 'moderate') || str_contains($clean, 'medium') || str_contains($clean, 'normal')) {
            return 'medium';
        }

        if (str_contains($clean, 'minor') || str_contains($clean, 'low')) {
            return 'low';
        }

        return null;
    }

    private function heuristicClassify(string $text): string
    {
        $subject = strtolower($text);

        $highPatterns = [
            '/\b(fire|smoke|gas leak|electrocution|short circuit|explosion)\b/',
            '/\b(theft|robbery|assault|harass|violence|threat|weapon)\b/',
            '/\b(injury|emergency|danger|unsafe|collapse)\b/',
            '/\b(urgent|immediately|asap|now)\b/',
        ];

        foreach ($highPatterns as $pattern) {
            if (preg_match($pattern, $subject) === 1) {
                return 'high';
            }
        }

        $mediumPatterns = [
            '/\b(leak|flood|plumbing|water outage|power outage|brownout)\b/',
            '/\b(broken|damaged|malfunction|not working|defective)\b/',
            '/\b(noise|internet|wifi|security concern|lock issue)\b/',
        ];

        foreach ($mediumPatterns as $pattern) {
            if (preg_match($pattern, $subject) === 1) {
                return 'medium';
            }
        }

        return 'low';
    }
}
