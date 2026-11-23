<?php

namespace App\Services\AI;

use App\Models\ChatbotConversation;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    protected AiPromptBuilder $promptBuilder;
    protected AiToolRegistry $toolRegistry;
    protected AiToolExecutor $toolExecutor;
    protected AiConversationService $conversationService;
    protected GeminiService $geminiService;

    public function __construct(
        AiPromptBuilder $promptBuilder,
        AiToolRegistry $toolRegistry,
        AiToolExecutor $toolExecutor,
        AiConversationService $conversationService,
        GeminiService $geminiService
    ) {
        $this->promptBuilder = $promptBuilder;
        $this->toolRegistry = $toolRegistry;
        $this->toolExecutor = $toolExecutor;
        $this->conversationService = $conversationService;
        $this->geminiService = $geminiService;
    }

    /**
     * Process a user's message and generate an AI-orchestrated response.
     *
     * @return array{reply: string, meta: array}
     */
    public function handleMessage(
        ChatbotConversation $conversation,
        User $user,
        string $userContent,
        array $geoContext = []
    ): array {
        $startTime = microtime(true);
        $model = $this->geminiService->chatModel();
        $timeout = (int) config('services.gemini.timeout', 30);
        $maxIterations = (int) config('chatbot.max_tool_iterations', 4);
        $historyLimit = (int) config('chatbot.history_message_limit', 8);

        if (!$this->geminiService->isConfigured()) {
            Log::warning("AI Chatbot: Gemini API key is missing in configuration (GEMINI_API_KEY).");
            $fallbackReply = 'The assistant is temporarily offline for maintenance. Please check your configuration.';
            $this->conversationService->recordAssistantReply($conversation, $fallbackReply, []);
            return ['reply' => $fallbackReply, 'meta' => []];
        }

        $systemPrompt = $this->promptBuilder->buildSystemPrompt($user, $geoContext);
        $recentHistory = $this->conversationService->getRecentChatHistory($conversation, $historyLimit);

        // Remove the current user message if it was just stored, so it isn't duplicated
        if (!empty($recentHistory)) {
            $last = end($recentHistory);
            if ($last['role'] === 'user' && trim($last['content']) === trim($userContent)) {
                array_pop($recentHistory);
            }
        }

        $contents = [];
        foreach ($recentHistory as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $msg['content']],
                ],
            ];
        }
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $userContent],
            ],
        ];

        $rawTools = $this->toolRegistry->getToolsForRole($user->role);
        $functionDeclarations = GeminiService::convertOpenAiToolsToGemini($rawTools);

        $toolsInvoked = [];
        $collectedActions = [];
        $finalReply = null;
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $iteration++;

            $payload = [
                'contents' => $contents,
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemPrompt],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                ],
            ];

            if (!empty($functionDeclarations)) {
                $payload['tools'] = [
                    [
                        'functionDeclarations' => $functionDeclarations,
                    ],
                ];
            }

            try {
                $data = $this->geminiService->postGenerateContent($model, $payload, $timeout);

                if ($data === null) {
                    return $this->returnFallback($conversation, 'Sorry, I could not reach the assistant right now. Please try again shortly.');
                }

                $candidate = $data['candidates'][0] ?? null;
                $candidateContent = $candidate['content'] ?? null;
                $parts = $candidateContent['parts'] ?? null;

                if ((!is_array($parts) || empty($parts)) && !isset($data['choices'][0]['message'])) {
                    Log::error("AI Chatbot: Invalid response format from Gemini provider.", ['data' => $data]);
                    return $this->returnFallback($conversation, 'Sorry, I could not generate a response. Please try again.');
                }

                // Check for native Gemini functionCall parts
                $functionCalls = [];
                $textParts = [];

                if (is_array($parts)) {
                    foreach ($parts as $part) {
                        if (isset($part['functionCall']) && is_array($part['functionCall'])) {
                            $functionCalls[] = $part['functionCall'];
                        }
                        if (isset($part['text']) && is_string($part['text'])) {
                            $textParts[] = $part['text'];
                        }
                    }
                } elseif (isset($data['choices'][0]['message'])) {
                    $msg = $data['choices'][0]['message'];
                    if (!empty($msg['tool_calls']) && is_array($msg['tool_calls'])) {
                        foreach ($msg['tool_calls'] as $tc) {
                            $rawArgs = $tc['function']['arguments'] ?? [];
                            $functionCalls[] = [
                                'name' => $tc['function']['name'] ?? '',
                                'args' => is_string($rawArgs) ? (json_decode($rawArgs, true) ?: []) : (array) $rawArgs,
                            ];
                        }
                    }
                    if (!empty($msg['content']) && is_string($msg['content'])) {
                        $textParts[] = $msg['content'];
                    }
                }

                if (!empty($functionCalls)) {
                    // Record model turn with its function call parts
                    $contents[] = [
                        'role' => 'model',
                        'parts' => !empty($parts) ? $parts : array_map(fn ($fc) => ['functionCall' => $fc], $functionCalls),
                    ];

                    foreach ($functionCalls as $toolCall) {
                        $funcName = $toolCall['name'] ?? '';
                        $rawArgs = $toolCall['args'] ?? [];
                        $args = is_array($rawArgs) ? $rawArgs : (json_decode($rawArgs, true) ?: []);

                        // Inject geo context if tool is nearest rooms and coordinates exist
                        if ($funcName === 'get_nearest_available_rooms' && !empty($geoContext['lat']) && !empty($geoContext['lng'])) {
                            $args['latitude'] = $args['latitude'] ?? $geoContext['lat'];
                            $args['longitude'] = $args['longitude'] ?? $geoContext['lng'];
                        }

                        $toolStart = microtime(true);
                        $result = $this->toolExecutor->execute($funcName, $args, $user);
                        $toolDuration = round((microtime(true) - $toolStart) * 1000, 2);

                        $toolsInvoked[] = [
                            'tool' => $funcName,
                            'duration_ms' => $toolDuration,
                        ];

                        $this->extractActionsFromResult($result, $collectedActions, $userContent);

                        $contents[] = [
                            'role' => 'function',
                            'parts' => [
                                [
                                    'functionResponse' => [
                                        'name' => $funcName,
                                        'response' => is_array($result) ? $result : ['output' => $result],
                                    ],
                                ],
                            ],
                        ];
                    }

                    // Loop continues to allow Gemini to analyze tool outputs
                    continue;
                }

                $joinedText = trim(implode("\n", $textParts));

                // Check if the content contains a structured JSON tool call fallback
                $fallbackToolCall = $this->parseJsonToolCallFallback($joinedText);

                if ($fallbackToolCall !== null) {
                    $funcName = $fallbackToolCall['tool'];
                    $args = $fallbackToolCall['arguments'];

                    if ($funcName === 'get_nearest_available_rooms' && !empty($geoContext['lat']) && !empty($geoContext['lng'])) {
                        $args['latitude'] = $args['latitude'] ?? $geoContext['lat'];
                        $args['longitude'] = $args['longitude'] ?? $geoContext['lng'];
                    }

                    $toolStart = microtime(true);
                    $result = $this->toolExecutor->execute($funcName, $args, $user);
                    $toolDuration = round((microtime(true) - $toolStart) * 1000, 2);

                    $toolsInvoked[] = [
                        'tool' => $funcName,
                        'duration_ms' => $toolDuration,
                        'fallback' => true,
                    ];

                    $this->extractActionsFromResult($result, $collectedActions, $userContent);

                    $contents[] = [
                        'role' => 'model',
                        'parts' => [
                            ['text' => $joinedText],
                        ],
                    ];
                    $contents[] = [
                        'role' => 'user',
                        'parts' => [
                            ['text' => 'Tool result for ' . $funcName . ': ' . json_encode($result)],
                        ],
                    ];

                    continue;
                }

                // Natural language answer reached
                $finalReply = $joinedText;
                break;

            } catch (\Throwable $e) {
                Log::error("AI Chatbot Exception: " . $e->getMessage(), [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'exception' => $e,
                ]);

                return $this->returnFallback($conversation, 'The assistant is temporarily unavailable. Please try again shortly.');
            }
        }

        if (empty($finalReply)) {
            $finalReply = 'I gathered the requested system information, but could not finalize the response. Please ask again.';
        }

        // Build response metadata for UI actions
        $meta = $this->buildResponseMeta($collectedActions, $user, $finalReply);

        // Record assistant reply in database
        $this->conversationService->recordAssistantReply($conversation, $finalReply, $meta);

        // Safe operational performance logging
        $totalDuration = round((microtime(true) - $startTime) * 1000, 2);
        Log::info("AI Chatbot Request Completed", [
            'user_id' => $user->id,
            'role' => $user->role,
            'conversation_id' => $conversation->id,
            'duration_ms' => $totalDuration,
            'iterations' => $iteration,
            'tools_invoked' => $toolsInvoked,
        ]);

        return [
            'reply' => $finalReply,
            'meta' => $meta,
        ];
    }

    /**
     * Parse structured JSON tool call fallback if generated inside text content.
     */
    protected function parseJsonToolCallFallback(string $content): ?array
    {
        if (!str_starts_with($content, '{') || !str_ends_with($content, '}')) {
            return null;
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return null;
        }

        if (isset($decoded['type']) && $decoded['type'] === 'tool_call' && !empty($decoded['tool'])) {
            return [
                'tool' => (string) $decoded['tool'],
                'arguments' => (array) ($decoded['arguments'] ?? []),
            ];
        }

        if (isset($decoded['tool']) && is_string($decoded['tool'])) {
            return [
                'tool' => (string) $decoded['tool'],
                'arguments' => (array) ($decoded['arguments'] ?? []),
            ];
        }

        return null;
    }

    public const ROUTE_LABELS = [
        '/admin/dashboard' => 'Dashboard',
        '/admin/student-verifications' => 'Review Students',
        '/admin/landlord-verifications' => 'Review Landlords',
        '/admin/approvals/landlords' => 'Review Landlord Documents',
        '/admin/permits' => 'Review Permits',
        '/admin/properties' => 'View Properties',
        '/admin/properties/approval' => 'Property Approvals',
        '/admin/bookings' => 'View Bookings',
        '/admin/boarding-monitoring' => 'Boarding Monitoring',
        '/admin/boarded-students' => 'Boarded Students',
        '/admin/onboardings' => 'View Onboardings',
        '/admin/reports' => 'View Reports',
        '/admin/users' => 'Manage Users',
        '/admin/settings' => 'System Settings',
        '/admin/notifications' => 'Notifications',
        '/student/dashboard' => 'Dashboard',
        '/student/tenant-dashboard' => 'Tenant Dashboard',
        '/student/rooms' => 'Browse Rooms',
        '/student/properties/map' => 'Property Map',
        '/student/requests' => 'My Requests',
        '/student/bookings' => 'My Bookings',
        '/student/onboarding' => 'Tenant Onboarding',
        '/student/payments' => 'My Payments',
        '/student/reports' => 'My Reports',
        '/student/profile' => 'My Profile',
        '/student/setup' => 'Student Verification',
        '/student/notifications' => 'Notifications',
        '/landlord/dashboard' => 'Dashboard',
        '/landlord/setup' => 'Landlord Setup',
        '/landlord/properties' => 'My Properties',
        '/landlord/rooms' => 'Manage Rooms',
        '/landlord/bookings' => 'Booking Requests',
        '/landlord/tenants' => 'My Tenants',
        '/landlord/onboarding' => 'Tenant Onboardings',
        '/landlord/payments' => 'Tenant Payments',
        '/landlord/leave-requests' => 'Leave Requests',
        '/landlord/maintenance' => 'Maintenance',
        '/landlord/analytics' => 'Analytics',
        '/landlord/feedback' => 'Tenant Feedback',
        '/landlord/notifications' => 'Notifications',
        '/notifications' => 'Notifications',
        '/messages' => 'Messages',
    ];

    /**
     * Resolve short, user-friendly label for an internal route.
     */
    public static function resolveLabelForRoute(string $url): string
    {
        $cleanUrl = strtok(trim($url), '?#');
        if (isset(self::ROUTE_LABELS[$cleanUrl])) {
            return self::ROUTE_LABELS[$cleanUrl];
        }

        if (preg_match('#^/student/rooms/\d+$#', $cleanUrl)) {
            return 'View Room';
        }

        if (preg_match('#^/admin/properties/\d+$#', $cleanUrl)) {
            return 'View Property';
        }

        // Generic fallback: convert slug to Title Case
        $segments = array_filter(explode('/', $cleanUrl));
        $last = end($segments) ?: 'Open';
        $title = ucwords(str_replace(['-', '_'], ' ', $last));

        return $title ?: 'View Details';
    }

    /**
     * Validate if an internal route is authorized for a specific role.
     */
    public static function isRouteAuthorizedForRole(string $url, string $role): bool
    {
        $clean = trim($url);

        // Must start with / and not contain protocol or double slashes
        if (!str_starts_with($clean, '/') || str_starts_with($clean, '//')) {
            return false;
        }

        if (preg_match('#^(javascript:|data:|http:|https:|file:)#i', $clean)) {
            return false;
        }

        $role = strtolower(trim($role));

        if ($role === 'student') {
            return str_starts_with($clean, '/student')
                || $clean === '/notifications'
                || $clean === '/messages';
        }

        if ($role === 'landlord') {
            return str_starts_with($clean, '/landlord')
                || $clean === '/notifications'
                || $clean === '/messages';
        }

        if ($role === 'admin') {
            return str_starts_with($clean, '/admin')
                || $clean === '/notifications'
                || $clean === '/messages';
        }

        return false;
    }

    /**
     * Extract navigational and UI action links from tool results.
     */
    protected function extractActionsFromResult(mixed $result, array &$collectedActions, string $prompt): void
    {
        if (!is_array($result)) {
            return;
        }

        if (!empty($result['requires_geo'])) {
            $collectedActions['geo'] = [
                'type' => 'geo',
                'label' => 'Share location',
                'prompt' => $prompt,
            ];
        }

        if (!empty($result['action_url']) && is_string($result['action_url'])) {
            $url = $result['action_url'];
            $collectedActions['links'][] = [
                'type' => 'internal_route',
                'label' => $result['action_label'] ?? self::resolveLabelForRoute($url),
                'route' => $url,
                'url' => $url,
            ];
        }

        if (!empty($result['action_urls']) && is_array($result['action_urls'])) {
            foreach ($result['action_urls'] as $url) {
                if (is_string($url)) {
                    $collectedActions['links'][] = [
                        'type' => 'internal_route',
                        'label' => self::resolveLabelForRoute($url),
                        'route' => $url,
                        'url' => $url,
                    ];
                }
            }
        }

        if (!empty($result['rooms']) && is_array($result['rooms'])) {
            foreach (array_slice($result['rooms'], 0, 3) as $r) {
                if (!empty($r['action_url'])) {
                    $collectedActions['links'][] = [
                        'type' => 'internal_route',
                        'label' => ($r['property_name'] ?? 'Room') . ' #' . ($r['room_number'] ?? ''),
                        'route' => $r['action_url'],
                        'url' => $r['action_url'],
                    ];
                }
            }
        }

        if (!empty($result['nearest_rooms']) && is_array($result['nearest_rooms'])) {
            foreach (array_slice($result['nearest_rooms'], 0, 3) as $r) {
                if (!empty($r['action_url'])) {
                    $collectedActions['links'][] = [
                        'type' => 'internal_route',
                        'label' => ($r['property_name'] ?? 'Room') . ' #' . ($r['room_number'] ?? '') . ' (' . ($r['distance_km'] ?? '?') . ' km)',
                        'route' => $r['action_url'],
                        'url' => $r['action_url'],
                    ];
                }
            }
        }
    }

    /**
     * Build UI meta object conforming to components/chatbot.blade.php contract.
     */
    protected function buildResponseMeta(array $collectedActions, User $user, string &$reply): array
    {
        $meta = [];

        if (!empty($collectedActions['geo'])) {
            $meta['action'] = $collectedActions['geo'];
            $meta['actions'] = [$collectedActions['geo']];
            return $meta;
        }

        $links = $collectedActions['links'] ?? [];

        // Also scan reply text for any internal OBHS paths mentioned directly by the LLM
        if (preg_match_all('#(?:\s*(?:sa|at|in|to)\s+)?`?(/(?:admin|student|landlord|notifications|messages)/[a-zA-Z0-9_\-\/]+)`?#i', $reply, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $idx => $matchedPath) {
                $fullMatchedText = $matches[0][$idx][0];
                $cleanUrl = strtok($matchedPath[0], '.,;?!)]}"\'');
                if (self::isRouteAuthorizedForRole($cleanUrl, $user->role)) {
                    $label = self::resolveLabelForRoute($cleanUrl);
                    $links[] = [
                        'type' => 'internal_route',
                        'label' => $label,
                        'route' => $cleanUrl,
                        'url' => $cleanUrl,
                    ];

                    // If the reply already mentions the page label nearby, remove the raw path cleanly
                    if (stripos($reply, $label) !== false) {
                        $reply = str_replace($fullMatchedText, '', $reply);
                    } else {
                        $reply = str_replace($fullMatchedText, " **{$label}**", $reply);
                    }
                }
            }
            $reply = (string) preg_replace('/\s+([.,!?])/', '$1', $reply);
        }

        // Filter links strictly by caller role authorization and de-duplicate by URL
        $seen = [];
        $authorized = [];
        foreach ($links as $link) {
            $url = $link['url'] ?? $link['route'] ?? '';
            if ($url === '' || isset($seen[$url])) {
                continue;
            }
            if (self::isRouteAuthorizedForRole($url, $user->role)) {
                $seen[$url] = true;
                $authorized[] = [
                    'type' => $link['type'] ?? 'internal_route',
                    'label' => $link['label'] ?? self::resolveLabelForRoute($url),
                    'route' => $url,
                    'url' => $url,
                ];
            }
        }

        if (!empty($authorized)) {
            $meta['action'] = $authorized[0];
            $meta['actions'] = array_slice($authorized, 0, 4);
        }

        return $meta;
    }

    /**
     * Fallback helper on errors.
     */
    protected function returnFallback(ChatbotConversation $conversation, string $message): array
    {
        $this->conversationService->recordAssistantReply($conversation, $message, []);
        return [
            'reply' => $message,
            'meta' => [],
        ];
    }
}
