<?php

namespace App\Http\Controllers;

use App\Services\AI\AiAssistantService;
use App\Services\AI\AiConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    /**
     * Retrieve the conversation history for the authenticated user.
     */
    public function history(AiConversationService $conversationService): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $conversation = $conversationService->getOrCreateConversation($user);

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $conversation->messages()->get(['id', 'role', 'content', 'meta', 'created_at']),
        ]);
    }

    /**
     * Send a message to the AI-orchestrated assistant.
     */
    public function message(
        Request $request,
        AiAssistantService $aiAssistant,
        AiConversationService $conversationService
    ): JsonResponse {
        $request->validate([
            'content' => 'required|string|max:2000',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $content = trim((string) $request->input('content'));

        $conversation = $conversationService->getOrCreateConversation($user);

        // Record incoming user message
        $conversationService->recordUserMessage($conversation, $content);

        // Prepare optional geographic coordinates context
        $geoContext = [];
        if ($request->filled('lat') && $request->filled('lng')) {
            $geoContext = [
                'lat' => (float) $request->input('lat'),
                'lng' => (float) $request->input('lng'),
            ];
        }

        // Delegate reasoning and tool execution to the AI assistant
        $response = $aiAssistant->handleMessage($conversation, $user, $content, $geoContext);

        $actions = $response['meta']['actions'] ?? [];

        return response()->json([
            'reply' => $response['reply'],
            'message' => $response['reply'],
            'actions' => $actions,
            'meta' => $response['meta'] ?? [],
        ]);
    }
}
