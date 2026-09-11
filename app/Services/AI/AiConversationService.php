<?php

namespace App\Services\AI;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Models\User;

class AiConversationService
{
    /**
     * Get or create the conversation for the authenticated user.
     */
    public function getOrCreateConversation(User $user): ChatbotConversation
    {
        return ChatbotConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['role' => $user->role]
        );
    }

    /**
     * Record a user message in the conversation.
     */
    public function recordUserMessage(ChatbotConversation $conversation, string $content): ChatbotMessage
    {
        return ChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $content,
        ]);
    }

    /**
     * Record an assistant reply in the conversation.
     */
    public function recordAssistantReply(ChatbotConversation $conversation, string $content, array $meta = []): ChatbotMessage
    {
        return ChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $content,
            'meta' => $meta,
        ]);
    }

    /**
     * Get recent conversation messages formatted for chat completions.
     * Keeps history focused and turn-scoped.
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function getRecentChatHistory(ChatbotConversation $conversation, int $limit = 8): array
    {
        $limit = min(max($limit, 2), 10);

        $messages = $conversation->messages()
            ->latest('id')
            ->take($limit)
            ->get()
            ->reverse();

        $history = [];
        foreach ($messages as $msg) {
            $content = trim((string) $msg->content);
            if ($content === '') {
                continue;
            }

            // Strip out any raw JSON tool-call fallback blocks if stored in older history
            if (str_starts_with($content, '{') && str_ends_with($content, '}') && str_contains($content, '"tool"')) {
                continue;
            }

            $history[] = [
                'role' => $msg->role === 'assistant' ? 'assistant' : 'user',
                'content' => $content,
            ];
        }

        return $history;
    }
}
