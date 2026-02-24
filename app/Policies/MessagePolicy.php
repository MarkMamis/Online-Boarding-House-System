<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->role === 'admin' ? true : null;
    }

    public function view(User $user, Message $message): bool
    {
        return (int) $message->sender_id === (int) $user->id || (int) $message->receiver_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['student', 'landlord', 'admin'], true);
    }

    public function markRead(User $user, Message $message): bool
    {
        return (int) $message->receiver_id === (int) $user->id;
    }
}
