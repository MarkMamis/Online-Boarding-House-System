<?php

namespace App\Services\AI;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class AiToolExecutor
{
    protected AiToolRegistry $registry;

    public function __construct(AiToolRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Execute an approved AI tool with role authorization and validation.
     */
    public function execute(string $toolName, array $args, User $user): array
    {
        $tool = $this->registry->getTool($toolName);

        if (!$tool) {
            return [
                'error' => "Tool '{$toolName}' does not exist in the approved registry.",
            ];
        }

        // Authoritative role check
        if (!$this->registry->isAuthorized($toolName, $user->role)) {
            Log::warning("AI Chatbot Tool Authorization Violation: User #{$user->id} ({$user->role}) attempted to invoke tool '{$toolName}'.");
            return [
                'error' => "Unauthorized. Your role ({$user->role}) is not permitted to use '{$toolName}'.",
            ];
        }

        $handler = $tool['handler'] ?? null;
        if (!is_array($handler) || count($handler) !== 2) {
            return [
                'error' => "Tool '{$toolName}' handler is misconfigured.",
            ];
        }

        [$class, $method] = $handler;

        try {
            $instance = app()->make($class);
            $result = $instance->{$method}($user, $args);

            return $this->sanitizeOutput($result);
        } catch (\Throwable $e) {
            Log::error("AI Tool Execution Error on '{$toolName}': " . $e->getMessage(), [
                'user_id' => $user->id,
                'role' => $user->role,
                'args' => $args,
                'exception' => $e,
            ]);

            return [
                'error' => "Failed to execute '{$toolName}'. Please check your parameters and try again.",
            ];
        }
    }

    /**
     * Sanitize tool output to ensure sensitive fields are never leaked.
     */
    protected function sanitizeOutput(mixed $data): mixed
    {
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                if (in_array(strtolower((string) $key), ['password', 'remember_token', 'token', 'secret', 'hash'], true)) {
                    continue;
                }
                $cleaned[$key] = $this->sanitizeOutput($value);
            }
            return $cleaned;
        }

        return $data;
    }
}
