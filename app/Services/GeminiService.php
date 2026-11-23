<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiService
{
    protected ?string $apiKey;
    protected string $chatModel;
    protected string $classificationModel;
    protected string $baseUrl;
    protected int $defaultTimeout;

    public function __construct(
        ?string $apiKey = null,
        ?string $chatModel = null,
        ?string $classificationModel = null,
        ?string $baseUrl = null,
        ?int $defaultTimeout = null
    ) {
        $this->apiKey = $apiKey ?? config('services.gemini.api_key');
        $this->chatModel = $chatModel ?? (string) config('services.gemini.chat_model', 'gemini-3.8-flash');
        $this->classificationModel = $classificationModel ?? (string) config('services.gemini.classification_model', 'gemini-3.5-flash-lite');
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->defaultTimeout = $defaultTimeout ?? (int) config('services.gemini.timeout', 30);
    }

    /**
     * Check whether the Gemini API key is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get the configured chat model identifier.
     */
    public function chatModel(): string
    {
        return $this->chatModel;
    }

    /**
     * Get the configured classification model identifier.
     */
    public function classificationModel(): string
    {
        return $this->classificationModel;
    }

    /**
     * Perform structured single-label classification using Gemini.
     * Guaranteed to return one of the $allowedLabels, or null on failure.
     *
     * @param string $text
     * @param array<string> $allowedLabels
     * @param string|null $systemInstruction
     * @param string|null $model
     * @param int $timeout
     * @return string|null
     */
    public function classify(
        string $text,
        array $allowedLabels,
        ?string $systemInstruction = null,
        ?string $model = null,
        int $timeout = 15
    ): ?string {
        $content = trim($text);
        if ($content === '' || !$this->isConfigured() || empty($allowedLabels)) {
            return null;
        }

        $targetModel = $model ?: $this->classificationModel;
        $instruction = $systemInstruction ?: 'Classify the provided text into exactly one of the allowed labels: ' . implode(', ', $allowedLabels) . '.';

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $content],
                    ],
                ],
            ],
            'systemInstruction' => [
                'parts' => [
                    ['text' => $instruction],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.0,
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'label' => [
                            'type' => 'STRING',
                            'enum' => array_values($allowedLabels),
                        ],
                    ],
                    'required' => ['label'],
                ],
            ],
        ];

        $response = $this->postGenerateContent($targetModel, $payload, $timeout);
        if ($response === null) {
            return null;
        }

        $rawText = $response['candidates'][0]['content']['parts'][0]['text']
            ?? $response['choices'][0]['message']['content']
            ?? null;
        if (!is_string($rawText) || trim($rawText) === '') {
            return null;
        }

        $decoded = json_decode(trim($rawText), true);
        if (is_array($decoded) && isset($decoded['label'])) {
            $label = strtolower(trim((string) $decoded['label']));
            if (in_array($label, array_map('strtolower', $allowedLabels), true)) {
                return $label;
            }
        }

        // Fallback: check if the string itself matches an allowed label
        $normalized = strtolower(trim($rawText));
        foreach ($allowedLabels as $allowed) {
            if ($normalized === strtolower($allowed) || preg_match('/\b' . preg_quote(strtolower($allowed), '/') . '\b/', $normalized)) {
                return strtolower($allowed);
            }
        }

        return null;
    }

    /**
     * Send a generateContent request to Gemini with bounded retries on transient errors.
     *
     * @param string $model
     * @param array $payload
     * @param int $timeout
     * @return array|null
     */
    public function postGenerateContent(string $model, array $payload, int $timeout = 30): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('Gemini API: Cannot make request because API key is not configured.');
            return null;
        }

        $endpoint = "{$this->baseUrl}/models/{$model}:generateContent";
        $maxAttempts = 2;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                $response = Http::withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                ])
                    ->acceptJson()
                    ->timeout($timeout)
                    ->post($endpoint, $payload);

                if ($response->successful()) {
                    return $response->json();
                }

                $status = $response->status();

                // Handle model not found explicitly - do not retry and do not silently switch
                if ($status === 404) {
                    Log::error("Gemini API: Configured model '{$model}' is unavailable or not found (HTTP 404).");
                    return null;
                }

                // Client errors (400, 401, 403, 422) - do not retry
                if ($status >= 400 && $status < 500 && $status !== 429) {
                    Log::warning("Gemini API client error: status {$status} on attempt {$attempt}.");
                    return null;
                }

                // Rate limit (429) or Server error (5xx) - bounded retry
                if ($status === 429) {
                    $retryAfter = (int) $response->header('Retry-After', 1);
                    $waitMs = min(max($retryAfter * 1000, 300), 2000);
                    Log::warning("Gemini API: Rate limit (429) encountered on attempt {$attempt}. Backing off for {$waitMs}ms.");
                } else {
                    $waitMs = 300;
                    Log::warning("Gemini API: HTTP {$status} on attempt {$attempt}. Retrying once.");
                }

                if ($attempt < $maxAttempts) {
                    usleep($waitMs * 1000);
                    continue;
                }

                return null;
            } catch (Throwable $e) {
                Log::warning("Gemini API: Request exception on attempt {$attempt}: " . $e->getMessage());

                if ($attempt < $maxAttempts) {
                    usleep(300000); // 300ms backoff
                    continue;
                }

                return null;
            }
        }

        return null;
    }

    /**
     * Convert an array of OpenAI-formatted function schemas to Gemini functionDeclarations format.
     *
     * @param array $openAiTools
     * @return array
     */
    public static function convertOpenAiToolsToGemini(array $openAiTools): array
    {
        $declarations = [];

        foreach ($openAiTools as $tool) {
            $func = $tool['function'] ?? $tool;
            $name = $func['name'] ?? null;
            if (!$name) {
                continue;
            }

            $parameters = $func['parameters'] ?? [];

            $declarations[] = [
                'name' => $name,
                'description' => $func['description'] ?? '',
                'parameters' => self::normalizeSchemaForGemini($parameters),
            ];
        }

        return $declarations;
    }

    /**
     * Recursively normalize OpenAPI / JSON schema types to uppercase for Gemini API.
     */
    protected static function normalizeSchemaForGemini(mixed $schema): mixed
    {
        if (!is_array($schema)) {
            return $schema;
        }

        $normalized = [];

        foreach ($schema as $key => $value) {
            if ($key === 'type' && is_string($value)) {
                $normalized[$key] = strtoupper($value);
            } elseif ($key === 'properties' && is_array($value)) {
                $props = [];
                foreach ($value as $propName => $propDef) {
                    $props[$propName] = self::normalizeSchemaForGemini($propDef);
                }
                $normalized[$key] = empty($props) ? (object) [] : $props;
            } elseif ($key === 'items' && is_array($value)) {
                $normalized[$key] = self::normalizeSchemaForGemini($value);
            } else {
                $normalized[$key] = is_array($value) ? self::normalizeSchemaForGemini($value) : $value;
            }
        }

        if (isset($normalized['type']) && $normalized['type'] === 'OBJECT' && !isset($normalized['properties'])) {
            $normalized['properties'] = (object) [];
        }

        return $normalized;
    }
}
