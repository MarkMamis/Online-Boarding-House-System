<?php

namespace App\Services\AI\Tools;

use App\Models\User;

class UserTools
{
    /**
     * Count users filtered by role or status (Admin only).
     */
    public function countUsers(User $user, array $args): array
    {
        $query = User::query();

        $role = strtolower(trim((string) ($args['role'] ?? 'all')));
        if ($role !== '' && $role !== 'all') {
            $query->where('role', $role);
        }

        $status = strtolower(trim((string) ($args['status'] ?? 'all')));
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $count = $query->count();

        return [
            'total_count' => $count,
            'role_filter' => $role,
            'status_filter' => $status,
        ];
    }

    /**
     * Get aggregate statistics on users (Admin only).
     */
    public function getUserStatistics(User $user, array $args): array
    {
        $totalUsers = User::count();
        $studentsCount = User::where('role', 'student')->count();
        $landlordsCount = User::where('role', 'landlord')->count();
        $adminsCount = User::where('role', 'admin')->count();
        $activeCount = User::where('is_active', true)->count();
        $inactiveCount = User::where('is_active', false)->count();
        $verifiedEmailsCount = User::whereNotNull('email_verified_at')->count();

        return [
            'total_users' => $totalUsers,
            'students_count' => $studentsCount,
            'landlords_count' => $landlordsCount,
            'admins_count' => $adminsCount,
            'active_users' => $activeCount,
            'inactive_users' => $inactiveCount,
            'email_verified_users' => $verifiedEmailsCount,
        ];
    }

    /**
     * Get current authenticated user profile summary.
     */
    public function getCurrentUserSummary(User $user, array $args): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'full_name' => $user->full_name ?: $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => (bool) $user->is_active,
            'is_email_verified' => $user->hasVerifiedEmail(),
            'setup_completed' => $user->role === 'student' ? $user->isStudentSetupComplete() : true,
            'member_since' => $user->created_at?->toFormattedDateString(),
        ];
    }

    /**
     * Get recent notifications for authenticated user.
     */
    public function getMyNotifications(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 10);

        $unreadCount = $user->unreadNotifications()->count();
        $totalCount = $user->notifications()->count();

        $recent = $user->notifications()
            ->latest()
            ->take($limit)
            ->get(['id', 'data', 'read_at', 'created_at'])
            ->map(function ($notification) {
                $data = (array) $notification->data;
                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? $data['subject'] ?? 'Notification',
                    'message' => $data['message'] ?? $data['body'] ?? '',
                    'read' => $notification->read_at !== null,
                    'created_at' => $notification->created_at?->diffForHumans(),
                ];
            })
            ->values()
            ->all();

        return [
            'unread_count' => $unreadCount,
            'total_count' => $totalCount,
            'recent_notifications' => $recent,
        ];
    }

    /**
     * Search users by name or email (Admin only).
     */
    public function searchUsers(User $user, array $args): array
    {
        $queryText = trim((string) ($args['query'] ?? ''));
        $role = strtolower(trim((string) ($args['role'] ?? 'all')));
        $limit = min(max((int) ($args['limit'] ?? 10), 1), 20);

        $builder = User::query();

        if ($role !== '' && $role !== 'all') {
            $builder->where('role', $role);
        }

        if ($queryText !== '') {
            $builder->where(function ($q) use ($queryText) {
                $q->where('name', 'like', "%{$queryText}%")
                  ->orWhere('full_name', 'like', "%{$queryText}%")
                  ->orWhere('email', 'like', "%{$queryText}%");
            });
        }

        $results = $builder->latest()
            ->take($limit)
            ->get(['id', 'name', 'full_name', 'email', 'role', 'is_active', 'created_at'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->full_name ?: $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'is_active' => (bool) $u->is_active,
                'joined_at' => $u->created_at?->toFormattedDateString(),
            ])
            ->values()
            ->all();

        return [
            'query' => $queryText,
            'total_matches' => count($results),
            'users' => $results,
        ];
    }
}
