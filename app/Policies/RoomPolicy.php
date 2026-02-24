<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->role === 'admin' ? true : null;
    }

    public function view(User $user, Room $room): bool
    {
        if ($user->role === 'student') {
            return true;
        }

        if ($user->role !== 'landlord') {
            return false;
        }

        $room->loadMissing('property');
        return $room->property && (int) $room->property->landlord_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'landlord';
    }

    public function update(User $user, Room $room): bool
    {
        if ($user->role !== 'landlord') {
            return false;
        }

        $room->loadMissing('property');
        return $room->property && (int) $room->property->landlord_id === (int) $user->id;
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->update($user, $room);
    }
}
