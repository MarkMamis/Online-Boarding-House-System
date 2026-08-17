<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Centralized expiration calculations for landlord documents.
 *
 * Expiration state is always CALCULATED from expiration_date — it is never
 * persisted, so it cannot become stale. Notifications (Phase 4) can reuse
 * this service directly.
 */
class DocumentExpirationService
{
    public const STATUS_VALID = 'valid';
    public const STATUS_EXPIRING_SOON = 'expiring_soon';
    public const STATUS_EXPIRED = 'expired';

    public const URGENCY_NORMAL = 'normal';
    public const URGENCY_WARNING_60 = 'warning_60';
    public const URGENCY_WARNING_30 = 'warning_30';
    public const URGENCY_CRITICAL_7 = 'critical_7';
    public const URGENCY_EXPIRED = 'expired';

    /**
     * Configured thresholds in days.
     */
    public function thresholds(): array
    {
        return (array) config('landlord_documents.expiration', [
            'valid_days' => 60,
            'warning_60' => 60,
            'warning_30' => 30,
            'critical_7' => 7,
        ]);
    }

    /**
     * Days until expiration (signed). Positive = future, 0 = today,
     * negative = expired that many days ago. Null when no expiration date.
     */
    public function daysUntilExpiration(?Carbon $expirationDate): ?int
    {
        if (!$expirationDate) {
            return null;
        }

        $today = Carbon::today()->startOfDay();
        $expirationDay = $expirationDate->copy()->startOfDay();

        // diffInDays($date, false) is signed relative to the receiver:
        // today->diffInDays(expiration) is positive when expiration is in the future.
        return (int) $today->diffInDays($expirationDay, false);
    }

    /**
     * Main expiration status: valid | expiring_soon | expired.
     * A null date returns 'valid' (no expiration constraint applies).
     */
    public function getExpirationStatus(?Carbon $expirationDate): string
    {
        $days = $this->daysUntilExpiration($expirationDate);

        if ($days === null) {
            return self::STATUS_VALID;
        }

        if ($days <= 0) {
            return self::STATUS_EXPIRED;
        }

        $threshold = (int) $this->thresholds()['valid_days'];

        return $days > $threshold ? self::STATUS_VALID : self::STATUS_EXPIRING_SOON;
    }

    /**
     * Urgency level: normal | warning_60 | warning_30 | critical_7 | expired.
     */
    public function getUrgency(?Carbon $expirationDate): string
    {
        $days = $this->daysUntilExpiration($expirationDate);

        if ($days === null) {
            return self::URGENCY_NORMAL;
        }

        if ($days <= 0) {
            return self::URGENCY_EXPIRED;
        }

        $t = $this->thresholds();

        if ($days > (int) $t['warning_60']) {
            return self::URGENCY_NORMAL;
        }

        if ($days > (int) $t['warning_30']) {
            return self::URGENCY_WARNING_60;
        }

        if ($days > (int) $t['critical_7']) {
            return self::URGENCY_WARNING_30;
        }

        return self::URGENCY_CRITICAL_7;
    }

    /**
     * Human-readable expiration label.
     */
    public function getExpirationLabel(?Carbon $expirationDate): string
    {
        $days = $this->daysUntilExpiration($expirationDate);

        if ($days === null) {
            return 'No expiration date';
        }

        if ($days <= 0) {
            $abs = abs($days);

            return $abs === 0
                ? 'Expired today'
                : 'Expired ' . $abs . ' ' . Str::plural('day', $abs) . ' ago';
        }

        if ($this->getExpirationStatus($expirationDate) === self::STATUS_VALID) {
            return 'Valid for ' . $days . ' more days';
        }

        return 'Expiring in ' . $days . ' day' . ($days === 1 ? '' : 's');
    }

    /**
     * Structured expiration information for views.
     *
     * @return array{status: string, urgency: string, days_remaining: int|null, label: string}
     */
    public function expirationInfo(?Carbon $expirationDate): array
    {
        $days = $this->daysUntilExpiration($expirationDate);

        return [
            'status' => $this->getExpirationStatus($expirationDate),
            'urgency' => $this->getUrgency($expirationDate),
            'days_remaining' => $days,
            'label' => $this->getExpirationLabel($expirationDate),
        ];
    }
}
