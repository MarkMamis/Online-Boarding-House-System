<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
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

        $routesText = collect($routesByRole[$role] ?? [])
            ->map(fn($r) => $r['label'] . ': ' . $r['path'])
            ->implode('\n');

        return "You are a role-scoped assistant for an Online Boarding House System.\n" .
            "Only answer questions about the product and its routes. Do not answer general knowledge.\n" .
            "Role: {$role}. You MUST refuse any request that involves other roles' routes or actions.\n" .
            "Available routes:\n{$routesText}\n" .
            "When the user asks where to do something, respond with a short answer and mention the relevant route path.";
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
}
