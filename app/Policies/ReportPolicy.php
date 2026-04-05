<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->role === 'admin' ? true : null;
    }

    public function create(User $user): bool
    {
        if ($user->role !== 'student') {
            return false;
        }

        return $user->bookings()
            ->where('status', 'approved')
            ->whereDate('check_in', '<=', now()->toDateString())
            ->exists();
    }

    public function view(User $user, Report $report): bool
    {
        return (int) $report->user_id === (int) $user->id;
    }

    public function update(User $user, Report $report): bool
    {
        // Admin handled by before().
        return false;
    }

    public function markResponseRead(User $user, Report $report): bool
    {
        return (int) $report->user_id === (int) $user->id;
    }
}
