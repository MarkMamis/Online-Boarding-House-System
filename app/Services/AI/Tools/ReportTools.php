<?php

namespace App\Services\AI\Tools;

use App\Models\Report;
use App\Models\User;
use App\Services\AI\Support\AiDateRangeResolver;
use Illuminate\Support\Str;

class ReportTools
{
    /**
     * Count reports by status, priority, and date period (Admin only).
     */
    public function countReports(User $user, array $args): array
    {
        $status = strtolower(trim((string) ($args['status'] ?? 'all')));
        $priority = strtolower(trim((string) ($args['priority'] ?? 'all')));
        $dateBasis = $this->resolveDateBasis($args, $status);

        $dateRange = AiDateRangeResolver::resolve($args);

        $baseQuery = Report::query();
        AiDateRangeResolver::applyToQuery($baseQuery, $dateBasis, $dateRange);

        $filteredQuery = clone $baseQuery;

        if ($status !== '' && $status !== 'all') {
            $filteredQuery->where('status', $status);
        }

        if ($priority !== '' && $priority !== 'all') {
            $filteredQuery->where('priority', $priority);
        }

        $totalInPeriod = (clone $baseQuery)->count();
        $matchingCount = $filteredQuery->count();
        $pending = (clone $baseQuery)->where('status', 'pending')->count();
        $inProgress = (clone $baseQuery)->where('status', 'in_progress')->count();
        $resolved = (clone $baseQuery)->where('status', 'resolved')->count();

        return [
            'period' => [
                'label' => $dateRange['label'],
                'date_from' => $dateRange['date_from'],
                'date_to' => $dateRange['date_to'],
                'date_from_formatted' => $dateRange['date_from_formatted'],
                'date_to_formatted' => $dateRange['date_to_formatted'],
                'date_basis' => $dateBasis,
                'is_filtered' => $dateRange['is_filtered'],
            ],
            'count' => $matchingCount,
            'total_reports_in_period' => $totalInPeriod,
            'pending_count' => $pending,
            'in_progress_count' => $inProgress,
            'resolved_count' => $resolved,
            'all_time_total' => Report::count(),
            'status_filter' => $status,
            'priority_filter' => $priority,
            'action_url' => '/admin/reports',
        ];
    }

    /**
     * Get pending and high-priority reports for admin review with optional date/priority filtering (Admin only).
     */
    public function getPendingReports(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 15);
        $priority = strtolower(trim((string) ($args['priority'] ?? 'all')));
        $dateBasis = $this->resolveDateBasis($args);

        $dateRange = AiDateRangeResolver::resolve($args);

        $query = Report::with('user')
            ->whereIn('status', ['pending', 'in_progress']);

        AiDateRangeResolver::applyToQuery($query, $dateBasis, $dateRange);

        if ($priority !== '' && $priority !== 'all') {
            $query->where('priority', $priority);
        }

        $totalMatching = (clone $query)->count();

        $reports = $query
            ->orderByRaw("CASE WHEN priority = 'high' THEN 1 WHEN priority = 'medium' THEN 2 ELSE 3 END")
            ->latest('id')
            ->take($limit)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'priority' => $r->priority,
                'status' => $r->status,
                'submitted_by' => $r->user?->full_name ?: $r->user?->name ?: 'Unknown',
                'created_at' => $r->created_at?->diffForHumans(),
                'submitted_at' => $r->created_at?->toIso8601String(),
            ])
            ->all();

        return [
            'period' => [
                'label' => $dateRange['label'],
                'date_from' => $dateRange['date_from'],
                'date_to' => $dateRange['date_to'],
                'is_filtered' => $dateRange['is_filtered'],
            ],
            'unresolved_count' => $totalMatching,
            'displayed_count' => count($reports),
            'priority_filter' => $priority,
            'reports' => $reports,
            'action_url' => '/admin/reports',
        ];
    }

    /**
     * Search and list reports with date, status, priority, and text search filters (Admin only).
     */
    public function searchReports(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 15);
        $searchQuery = trim((string) ($args['query'] ?? ''));
        $status = strtolower(trim((string) ($args['status'] ?? 'all')));
        $priority = strtolower(trim((string) ($args['priority'] ?? 'all')));
        $dateBasis = $this->resolveDateBasis($args, $status);

        $dateRange = AiDateRangeResolver::resolve($args);

        $query = Report::with('user');
        AiDateRangeResolver::applyToQuery($query, $dateBasis, $dateRange);

        if ($searchQuery !== '') {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%")
                    ->orWhere('description', 'like', "%{$searchQuery}%")
                    ->orWhereHas('user', function ($uq) use ($searchQuery) {
                        $uq->where('name', 'like', "%{$searchQuery}%")
                            ->orWhere('full_name', 'like', "%{$searchQuery}%");
                    });
            });
        }

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($priority !== '' && $priority !== 'all') {
            $query->where('priority', $priority);
        }

        $totalMatching = (clone $query)->count();

        $reports = $query->latest('id')
            ->take($limit)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'description_snippet' => Str::limit($r->description, 120),
                'status' => $r->status,
                'priority' => $r->priority,
                'submitted_by' => $r->user?->full_name ?: $r->user?->name ?: 'Unknown',
                'submitted_at' => $r->created_at?->format('M d, Y h:i A'),
                'resolved_at' => $r->resolved_at?->format('M d, Y h:i A'),
            ])
            ->all();

        return [
            'period' => [
                'label' => $dateRange['label'],
                'date_from' => $dateRange['date_from'],
                'date_to' => $dateRange['date_to'],
                'date_from_formatted' => $dateRange['date_from_formatted'],
                'date_to_formatted' => $dateRange['date_to_formatted'],
                'date_basis' => $dateBasis,
                'is_filtered' => $dateRange['is_filtered'],
            ],
            'total_matching' => $totalMatching,
            'displayed_count' => count($reports),
            'status_filter' => $status,
            'priority_filter' => $priority,
            'reports' => $reports,
            'action_url' => '/admin/reports',
        ];
    }

    /**
     * Get aggregate report statistics and priority breakdown with date filtering (Admin only).
     */
    public function getReportStatistics(User $user, array $args): array
    {
        $status = strtolower(trim((string) ($args['status'] ?? 'all')));
        $priority = strtolower(trim((string) ($args['priority'] ?? 'all')));
        $dateBasis = $this->resolveDateBasis($args, $status);

        $dateRange = AiDateRangeResolver::resolve($args);

        // Base query for the specified period and date column basis
        $baseQuery = Report::query();
        AiDateRangeResolver::applyToQuery($baseQuery, $dateBasis, $dateRange);

        // Filtered query if status or priority was specifically requested
        $filteredQuery = clone $baseQuery;
        if ($status !== '' && $status !== 'all') {
            $filteredQuery->where('status', $status);
        }
        if ($priority !== '' && $priority !== 'all') {
            $filteredQuery->where('priority', $priority);
        }

        $periodTotal = (clone $baseQuery)->count();
        $matchingCount = $filteredQuery->count();

        $pending = (clone $baseQuery)->where('status', 'pending')->count();
        $inProgress = (clone $baseQuery)->where('status', 'in_progress')->count();
        $resolved = (clone $baseQuery)->where('status', 'resolved')->count();

        $highPriority = (clone $baseQuery)->where('priority', 'high')->count();
        $mediumPriority = (clone $baseQuery)->where('priority', 'medium')->count();
        $lowPriority = (clone $baseQuery)->where('priority', 'low')->count();

        $resolutionRate = $periodTotal > 0 ? round(($resolved / $periodTotal) * 100, 1) : 100.0;

        return [
            'period' => [
                'label' => $dateRange['label'],
                'date_from' => $dateRange['date_from'],
                'date_to' => $dateRange['date_to'],
                'date_from_formatted' => $dateRange['date_from_formatted'],
                'date_to_formatted' => $dateRange['date_to_formatted'],
                'date_basis' => $dateBasis,
                'is_filtered' => $dateRange['is_filtered'],
            ],
            'total' => $matchingCount,
            'total_reports_in_period' => $periodTotal,
            'statuses' => [
                'pending' => $pending,
                'in_progress' => $inProgress,
                'resolved' => $resolved,
            ],
            'priorities' => [
                'high' => $highPriority,
                'medium' => $mediumPriority,
                'low' => $lowPriority,
            ],
            'all_time_total' => Report::count(),
            'all_time_unresolved' => Report::whereIn('status', ['pending', 'in_progress'])->count(),
            'status_filter' => $status,
            'priority_filter' => $priority,
            'resolution_rate_percent' => $resolutionRate,
            'action_url' => '/admin/reports',
        ];
    }

    /**
     * Get reports submitted by the current authenticated user (Student).
     */
    public function getMyReports(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 15);
        $status = strtolower(trim((string) ($args['status'] ?? 'all')));
        $dateBasis = $this->resolveDateBasis($args, $status);

        $dateRange = AiDateRangeResolver::resolve($args);

        $query = Report::where('user_id', $user->id);
        AiDateRangeResolver::applyToQuery($query, $dateBasis, $dateRange);

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        $total = (clone $query)->count();

        $reports = $query->latest('id')
            ->take($limit)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'status' => $r->status,
                'priority' => $r->priority,
                'admin_response' => $r->admin_response ?: 'No response yet.',
                'submitted_at' => $r->created_at?->toFormattedDateString(),
                'resolved_at' => $r->resolved_at?->toFormattedDateString(),
            ])
            ->all();

        return [
            'period' => [
                'label' => $dateRange['label'],
                'date_from' => $dateRange['date_from'],
                'date_to' => $dateRange['date_to'],
                'is_filtered' => $dateRange['is_filtered'],
            ],
            'total_submitted' => $total,
            'displayed_count' => count($reports),
            'status_filter' => $status,
            'reports' => $reports,
            'action_url' => '/student/reports',
        ];
    }

    /**
     * Resolve which database column to base the date filtering on.
     */
    protected function resolveDateBasis(array $args, string $status = 'all'): string
    {
        $basis = strtolower(trim((string) ($args['date_basis'] ?? '')));

        if ($basis === 'resolved' || $basis === 'resolved_at') {
            return 'resolved_at';
        }

        if ($basis === 'updated' || $basis === 'updated_at') {
            return 'updated_at';
        }

        if ($status === 'resolved' && (isset($args['date_basis']) && $args['date_basis'] === 'resolved_at')) {
            return 'resolved_at';
        }

        return 'created_at';
    }
}
