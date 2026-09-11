<?php

namespace App\Services\AI\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class AiDateRangeResolver
{
    /**
     * Resolve a semantic period or date range into deterministic Carbon start & end boundaries.
     *
     * @param array $args Tool arguments containing potential 'period', 'date_from', 'date_to', etc.
     * @param string|null $timezone Optional timezone string (defaults to app timezone or Asia/Manila)
     * @return array{
     *     label: string,
     *     start: Carbon|null,
     *     end: Carbon|null,
     *     date_from: string|null,
     *     date_to: string|null,
     *     date_from_formatted: string|null,
     *     date_to_formatted: string|null,
     *     timezone: string,
     *     is_filtered: bool
     * }
     */
    public static function resolve(array $args = [], ?string $timezone = null): array
    {
        $tz = $timezone ?: config('app.timezone') ?: env('APP_TIMEZONE', 'Asia/Manila');
        $now = Carbon::now($tz);

        $period = strtolower(trim((string) ($args['period'] ?? '')));
        $dateFromRaw = trim((string) ($args['date_from'] ?? ''));
        $dateToRaw = trim((string) ($args['date_to'] ?? ''));

        // Normalize period strings (convert spaces/hyphens to underscores)
        $normalizedPeriod = str_replace([' ', '-'], '_', $period);

        // 1. Check predefined semantic periods
        switch ($normalizedPeriod) {
            case 'today':
            case 'ngayon':
            case 'ngayong_araw':
            case 'current_day':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                return self::formatResult('today', $start, $end, $tz);

            case 'yesterday':
            case 'kahapon':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                return self::formatResult('yesterday', $start, $end, $tz);

            case 'this_week':
            case 'current_week':
            case 'ngayong_linggo':
                $start = $now->copy()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
                $end = $now->copy()->endOfDay();
                return self::formatResult('this_week', $start, $end, $tz);

            case 'last_week':
            case 'nakaraang_linggo':
            case 'past_week':
                $start = $now->copy()->subWeek()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
                $end = $now->copy()->subWeek()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();
                return self::formatResult('last_week', $start, $end, $tz);

            case 'this_month':
            case 'current_month':
            case 'ngayong_buwan':
                $start = $now->copy()->startOfMonth()->startOfDay();
                $end = $now->copy()->endOfDay();
                return self::formatResult('this_month', $start, $end, $tz);

            case 'last_month':
            case 'nakaraang_buwan':
            case 'past_month':
                $start = $now->copy()->subMonth()->startOfMonth()->startOfDay();
                $end = $now->copy()->subMonth()->endOfMonth()->endOfDay();
                return self::formatResult('last_month', $start, $end, $tz);

            case 'this_year':
            case 'current_year':
            case 'ngayong_taon':
                $start = $now->copy()->startOfYear()->startOfDay();
                $end = $now->copy()->endOfDay();
                return self::formatResult('this_year', $start, $end, $tz);

            case 'past_7_days':
            case 'last_7_days':
            case '7_days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                return self::formatResult('past_7_days', $start, $end, $tz);

            case 'past_30_days':
            case 'last_30_days':
            case '30_days':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                return self::formatResult('past_30_days', $start, $end, $tz);

            case 'all':
            case 'all_time':
            case 'lahat':
                return self::allTimeResult($tz);
        }

        // 2. If explicit date_from or date_to provided
        if ($dateFromRaw !== '' || $dateToRaw !== '') {
            $parsedStart = null;
            $parsedEnd = null;

            if ($dateFromRaw !== '') {
                try {
                    $parsedStart = Carbon::parse($dateFromRaw, $tz);
                    if (!preg_match('/\d{1,2}:\d{2}/', $dateFromRaw)) {
                        $parsedStart = $parsedStart->startOfDay();
                    }
                } catch (\Throwable) {
                    $parsedStart = null;
                }
            }

            if ($dateToRaw !== '') {
                try {
                    $parsedEnd = Carbon::parse($dateToRaw, $tz);
                    if (!preg_match('/\d{1,2}:\d{2}/', $dateToRaw)) {
                        $parsedEnd = $parsedEnd->endOfDay();
                    }
                } catch (\Throwable) {
                    $parsedEnd = null;
                }
            }

            // If only date_from is given without date_to, default date_to to the end of that day or now
            if ($parsedStart !== null && $parsedEnd === null) {
                // If start is today or past, check if start was a single date
                $parsedEnd = $parsedStart->copy()->endOfDay();
            }

            // If only date_to is given without date_from, default date_from to start of that month or start of that day
            if ($parsedStart === null && $parsedEnd !== null) {
                $parsedStart = $parsedEnd->copy()->startOfDay();
            }

            if ($parsedStart !== null && $parsedEnd !== null) {
                if ($parsedStart->greaterThan($parsedEnd)) {
                    $temp = $parsedStart;
                    $parsedStart = $parsedEnd->copy()->startOfDay();
                    $parsedEnd = $temp->copy()->endOfDay();
                }

                $label = 'custom';
                if ($parsedStart->isSameDay($parsedEnd)) {
                    if ($parsedStart->isToday()) {
                        $label = 'today';
                    } elseif ($parsedStart->isYesterday()) {
                        $label = 'yesterday';
                    } else {
                        $label = $parsedStart->format('F j, Y');
                    }
                }

                return self::formatResult($label, $parsedStart, $parsedEnd, $tz);
            }
        }

        // 3. Check if period itself was a raw date string (e.g. "2026-09-11" or "September 11")
        if ($period !== '' && $period !== 'all' && $period !== 'all_time') {
            try {
                $singleDate = Carbon::parse($period, $tz);
                $start = $singleDate->copy()->startOfDay();
                $end = $singleDate->copy()->endOfDay();
                $label = $start->isToday() ? 'today' : ($start->isYesterday() ? 'yesterday' : $start->format('F j, Y'));
                return self::formatResult($label, $start, $end, $tz);
            } catch (\Throwable) {
                // Not a parsable single date string
            }
        }

        // Default: No date filter applied (all-time)
        return self::allTimeResult($tz);
    }

    /**
     * Apply the resolved date range filter to an Eloquent / Query Builder query.
     */
    public static function applyToQuery(Builder $query, string $column, array $range): Builder
    {
        if (empty($range['is_filtered']) || empty($range['start']) || empty($range['end'])) {
            return $query;
        }

        /** @var Carbon $start */
        $start = $range['start'];
        /** @var Carbon $end */
        $end = $range['end'];

        return $query->whereBetween($column, [
            $start->toDateTimeString(),
            $end->toDateTimeString(),
        ]);
    }

    /**
     * Format a populated date range response structure.
     */
    protected static function formatResult(string $label, Carbon $start, Carbon $end, string $tz): array
    {
        return [
            'label' => $label,
            'start' => $start,
            'end' => $end,
            'date_from' => $start->toIso8601String(),
            'date_to' => $end->toIso8601String(),
            'date_from_formatted' => $start->format('F j, Y'),
            'date_to_formatted' => $end->format('F j, Y'),
            'timezone' => $tz,
            'is_filtered' => true,
        ];
    }

    /**
     * Format an all-time (unfiltered) response structure.
     */
    protected static function allTimeResult(string $tz): array
    {
        return [
            'label' => 'all_time',
            'start' => null,
            'end' => null,
            'date_from' => null,
            'date_to' => null,
            'date_from_formatted' => null,
            'date_to_formatted' => null,
            'timezone' => $tz,
            'is_filtered' => false,
        ];
    }
}
