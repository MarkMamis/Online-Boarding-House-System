<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->role === 'admin' ? true : null;
    }

    public function view(User $user, Property $property): bool
    {
        if ($user->role === 'student') {
            return true;
        }

        if ($user->role === 'landlord') {
            return (int) $property->landlord_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'landlord';
    }

    public function update(User $user, Property $property): bool
    {
        return $user->role === 'landlord' && (int) $property->landlord_id === (int) $user->id;
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->update($user, $property);
    }
}
