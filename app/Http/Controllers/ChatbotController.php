<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function history()
    {
        $user = Auth::user();

        $conversation = ChatbotConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['role' => $user->role]
        );

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $conversation->messages()->get(['id', 'role', 'content', 'meta', 'created_at']),
        ]);
    }

    public function message(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        $user = Auth::user();
        $content = trim($request->input('content'));

        $conversation = ChatbotConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['role' => $user->role]
        );

        ChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $content,
        ]);

        $role = $user->role;
        $routeHit = $this->findRouteAction($content, $role);

        $quickReply = $this->handleQuickIntent($content, $role, $request->input('lat'), $request->input('lng'));
        if ($quickReply) {
            return $this->storeAndRespond($conversation, $quickReply['reply'], $quickReply['meta']);
        }

        $localReply = $this->answerLocalSystemIntent($content, $user);
        if ($localReply) {
            return $this->storeAndRespond($conversation, $localReply['reply'], $localReply['meta'] ?? []);
        }

        if ($routeHit['blocked']) {
            $reply = 'Sorry, I can only help with ' . $role . ' actions. That request is outside your role.';
            return $this->storeAndRespond($conversation, $reply, ['blocked' => true]);
        }

        $systemPrompt = $this->buildSystemPrompt($role);
        $assistantReply = $this->callChatModel($systemPrompt, $content);

        $responseMeta = [];
        if ($routeHit['action']) {
            $responseMeta['action'] = $routeHit['action'];
        }

        return $this->storeAndRespond($conversation, $assistantReply, $responseMeta);
    }

    private function storeAndRespond(ChatbotConversation $conversation, string $reply, array $meta = [])
    {
        ChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $reply,
            'meta' => $meta,
        ]);

        return response()->json([
            'reply' => $reply,
            'meta' => $meta,
        ]);
    }

    private function callChatModel(string $systemPrompt, string $userPrompt): string
    {
        $token = config('services.huggingface.token');
        if (empty($token)) {
            return 'Chatbot is not configured yet. Please set HF_TOKEN in the environment.';
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->post('https://router.huggingface.co/v1/chat/completions', [
                    'model' => 'Qwen/Qwen2.5-7B-Instruct:together',
                    'stream' => false,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                ]);

            if (!$response->successful()) {
                return 'Sorry, I could not reach the assistant right now.';
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;
            if (!is_string($content) || trim($content) === '') {
                return 'Sorry, I could not generate a response.';
            }

            return trim($content);
        } catch (\Throwable $e) {
            return 'Sorry, I could not reach the assistant right now.';
        }
    }

    private function buildSystemPrompt(string $role): string
    {
        $routesByRole = config('chatbot.routes', []);
        $dictionary = config('chatbot.dictionary', []);

        $routesText = collect($routesByRole[$role] ?? [])
            ->map(fn($r) => $r['label'] . ': ' . $r['path'])
            ->implode('\n');

        $sharedRoutesText = $this->buildSharedRoutesText($routesByRole);

        $sections = [];
        $sections[] = $this->formatSection('System overview', $dictionary['system_overview'] ?? []);
        $sections[] = $this->formatSection('Role definitions', $dictionary['role_definitions'] ?? []);
        $sections[] = $this->formatSection('Shared features', $dictionary['shared_features'] ?? []);

        if ($role === 'student') {
            $sections[] = $this->formatSection('Student features', $dictionary['student_features'] ?? []);
            $sections[] = $this->formatSection('Tenant student features', $dictionary['tenant_student_features'] ?? []);
        } elseif ($role === 'landlord') {
            $sections[] = $this->formatSection('Landlord features', $dictionary['landlord_features'] ?? []);
        } elseif ($role === 'admin') {
            $sections[] = $this->formatSection('Admin features', $dictionary['admin_features'] ?? []);
        }

        $sections[] = $this->formatSection('Workflows', $dictionary['workflows'] ?? []);
        $sections[] = $this->formatSection('Page dictionary', $dictionary['page_dictionary'] ?? []);
        $sections[] = $this->formatSection('FAQ', $dictionary['faq_dictionary'] ?? []);
        $sections[] = $this->formatSection('Policy rules', $dictionary['policy_rules'] ?? []);
        $sections[] = $this->formatSection('Response style', $dictionary['response_style'] ?? []);
        $sections[] = $this->formatSection('Role restrictions', $dictionary['blocked_hints'] ?? []);

        $knowledgeBase = implode("\n", array_filter($sections));

        return "You are a role-scoped assistant for an Online Boarding House System.\n" .
            "Only answer questions about the product and its routes. Do not answer general knowledge.\n" .
            "Role: {$role}. You MUST refuse any request that involves other roles' routes or actions.\n" .
            "Available routes:\n{$routesText}\n" .
            "Shared routes:\n{$sharedRoutesText}\n" .
            $knowledgeBase . "\n" .
            "When the user asks where to do something, respond with a short answer and mention the relevant route path.";
    }

    private function formatSection(string $title, array $lines): string
    {
        $lines = array_values(array_filter($lines, fn ($line) => is_string($line) && trim($line) !== ''));
        if (empty($lines)) {
            return '';
        }

        $body = collect($lines)
            ->map(fn ($line) => '- ' . trim($line))
            ->implode("\n");

        return $title . ":\n" . $body;
    }

    private function buildSharedRoutesText(array $routesByRole): string
    {
        if (empty($routesByRole)) {
            return 'None';
        }

        $shared = null;
        foreach ($routesByRole as $roleRoutes) {
            $paths = collect($roleRoutes)
                ->map(fn ($route) => $route['path'] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $shared = $shared === null ? $paths : array_values(array_intersect($shared, $paths));
        }

        if (empty($shared)) {
            return 'None';
        }

        return implode("\n", $shared);
    }

    private function handleQuickIntent(string $content, string $role, $lat = null, $lng = null): ?array
    {
        $text = strtolower($content);

        if ($role === 'student' && $this->looksLikeCheapestQuery($text)) {
            $room = $this->getCheapestAvailableRoom();
            if (!$room) {
                return [
                    'reply' => 'Wala akong nakitang available rooms ngayon. Maaari kang mag-browse sa listahan.',
                    'meta' => ['action' => ['label' => 'Browse rooms', 'url' => '/student/rooms']],
                ];
            }

            $label = $room->property?->name
                ? $room->property->name . ' — Room ' . $room->room_number
                : 'Room ' . $room->room_number;

            $price = number_format((float) $room->price, 0);

            return [
                'reply' => "Pinakamurang available ngayon: {$label} (₱{$price}/mo).",
                'meta' => ['action' => ['label' => 'View details', 'url' => '/student/rooms/' . $room->id]],
            ];
        }

        if ($role === 'student' && $this->looksLikeNearestQuery($text)) {
            if ($lat === null || $lng === null) {
                return [
                    'reply' => 'Para mahanap ang pinakamalapit, i-share mo ang location mo.',
                    'meta' => [
                        'action' => [
                            'type' => 'geo',
                            'label' => 'Share location',
                            'prompt' => $content,
                        ],
                    ],
                ];
            }

            $nearest = $this->getNearestAvailableRooms((float) $lat, (float) $lng, 3);
            if ($nearest->isEmpty()) {
                return [
                    'reply' => 'Wala akong makitang available rooms na malapit ngayon. Subukan ang browse list.',
                    'meta' => ['action' => ['label' => 'Browse rooms', 'url' => '/student/rooms']],
                ];
            }

            $lines = $nearest->map(function ($item, $idx) {
                $room = $item['room'];
                $km = number_format($item['km'], 2);
                $label = $room->property?->name
                    ? $room->property->name . ' — Room ' . $room->room_number
                    : 'Room ' . $room->room_number;
                $price = number_format((float) $room->price, 0);
                return ($idx + 1) . '. ' . $label . " (₱{$price}/mo, {$km} km)";
            })->implode("\n");

            $actions = $nearest->map(function ($item) {
                $room = $item['room'];
                $label = $room->property?->name
                    ? $room->property->name . ' — Room ' . $room->room_number
                    : 'Room ' . $room->room_number;
                return [
                    'label' => $label,
                    'url' => '/student/rooms/' . $room->id,
                ];
            })->values()->all();

            return [
                'reply' => "Pinakamalapit na available rooms:\n{$lines}",
                'meta' => ['actions' => $actions],
            ];
        }

        return null;
    }

    private function looksLikeCheapestQuery(string $text): bool
    {
        $needles = [
            'pinaka murang',
            'pinakamurang',
            'murang room',
            'cheap room',
            'cheapest room',
            'lowest price',
            'pinaka mababa',
            'pinakamababa',
        ];

        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeNearestQuery(string $text): bool
    {
        $needles = [
            'pinaka malapit',
            'pinakamalapit',
            'malapit na room',
            'nearest room',
            'closest room',
            'pinaka malapit na room',
            'malapit na available',
        ];

        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function getCheapestAvailableRoom(): ?Room
    {
        $rooms = Room::with('property')
            ->where('status', '!=', 'maintenance')
            ->whereHas('property', function ($q) {
                $q->visibleToAudience();
            })
            ->orderBy('price')
            ->get();

        foreach ($rooms as $room) {
            if ($room->hasAvailableSlots()) {
                return $room;
            }
        }

        return null;
    }

    private function getNearestAvailableRooms(float $lat, float $lng, int $limit = 3)
    {
        $rooms = Room::with('property')
            ->where('status', '!=', 'maintenance')
            ->whereHas('property', function ($q) {
                                $q->visibleToAudience()
                  ->whereNotNull('latitude')
                  ->whereNotNull('longitude');
            })
            ->get();

        $candidates = [];
        foreach ($rooms as $room) {
            if (!$room->hasAvailableSlots()) {
                continue;
            }

            $pLat = (float) $room->property->latitude;
            $pLng = (float) $room->property->longitude;
            $km = $this->haversineKm($lat, $lng, $pLat, $pLng);
            $candidates[] = ['room' => $room, 'km' => $km];
        }

        usort($candidates, fn($a, $b) => $a['km'] <=> $b['km']);

        return collect($candidates)->take($limit);
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function findRouteAction(string $content, string $role): array
    {
        $text = strtolower($content);

        $routeMap = config('chatbot.route_map', []);
        $blockedHints = config('chatbot.blocked_hints', []);

        foreach ($blockedHints[$role] ?? [] as $blockedWord) {
            if (str_contains($text, $blockedWord)) {
                return ['blocked' => true, 'action' => null];
            }
        }

        $matched = null;
        foreach (($routeMap[$role] ?? []) as $needle => $action) {
            if (str_contains($text, $needle)) {
                $matched = $action;
                break;
            }
        }

        return ['blocked' => false, 'action' => $matched];
    }

    private function answerLocalSystemIntent(string $content, $user): ?array
    {
        $text = strtolower($content);
        $role = $user->role ?? 'student';
        $intentsByRole = config('chatbot.local_intents', []);

        $roleIntents = $intentsByRole[$role] ?? [];
        foreach ($roleIntents as $intentKey => $intent) {
            $keywords = $intent['keywords'] ?? [];
            if (!$this->containsAny($text, $keywords)) {
                continue;
            }

            $reply = (string) ($intent['reply'] ?? '');
            if ($role === 'student' && $intentKey === 'booking') {
                if ($this->studentHasActiveBooking($user->id)) {
                    $reply .= ' Booking another room may be disabled while your current stay is active, but you can still browse rooms for future reference.';
                }
            }

            $meta = [];
            if (!empty($intent['action']['url'])) {
                $meta['action'] = $intent['action'];
            }

            return ['reply' => trim($reply), 'meta' => $meta];
        }

        foreach ($intentsByRole as $otherRole => $otherIntents) {
            if ($otherRole === $role) {
                continue;
            }
            foreach ($otherIntents as $intent) {
                $keywords = $intent['keywords'] ?? [];
                if ($this->containsAny($text, $keywords)) {
                    $reply = 'Sorry, I can only help with ' . $role . ' actions. That request is outside your role.';
                    return ['reply' => $reply, 'meta' => ['blocked' => true]];
                }
            }
        }

        return null;
    }

    private function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($text, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    private function studentHasActiveBooking(int $studentId): bool
    {
        $today = now()->toDateString();

        return Booking::where('student_id', $studentId)
            ->where('status', 'approved')
            ->where('check_out', '>', $today)
            ->exists();
    }
}
