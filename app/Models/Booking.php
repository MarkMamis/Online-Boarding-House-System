<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'student_id',
        'status',
        'check_in',
        'check_out',
        'notes',
        'cancel_reason',
        'payment_status',
        'payment_date',
        'include_advance_payment',
        'occupancy_mode',
        'monthly_rent_amount',
        'next_payment_due_date',
        'last_overdue_notified_at',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'payment_date' => 'datetime',
        'include_advance_payment' => 'boolean',
        'monthly_rent_amount' => 'decimal:2',
        'next_payment_due_date' => 'date',
        'last_overdue_notified_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tenantOnboarding()
    {
        return $this->hasOne(TenantOnboarding::class);
    }

    public function tenantPayments()
    {
        return $this->hasMany(TenantPayment::class)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');
    }

    public function latestTenantPayment()
    {
        return $this->hasOne(TenantPayment::class)->latestOfMany('id');
    }

    public function latestSubmittedTenantPayment()
    {
        return $this->hasOne(TenantPayment::class)
            ->where('status', 'submitted')
            ->latestOfMany('id');
    }

    public function getDurationInDays()
    {
        return $this->check_in->diffInDays($this->check_out);
    }

    public function resolvePaymentDueDate(): ?Carbon
    {
        if ($this->next_payment_due_date instanceof Carbon) {
            return $this->next_payment_due_date->copy()->startOfDay();
        }

        if ($this->next_payment_due_date) {
            return Carbon::parse($this->next_payment_due_date)->startOfDay();
        }

        return $this->check_in ? Carbon::parse($this->check_in)->startOfDay() : null;
    }

    public function derivedPaymentStatus(?Carbon $asOf = null): string
    {
        $asOfDate = ($asOf ?: now())->copy()->startOfDay();
        $dueDate = $this->resolvePaymentDueDate();

        if (!$dueDate) {
            return strtolower((string) ($this->payment_status ?? 'pending')) === 'paid'
                ? 'paid'
                : 'pending';
        }

        if (strtolower((string) ($this->payment_status ?? 'pending')) === 'paid') {
            if ($dueDate->gt($asOfDate)) {
                return 'paid';
            }

            if ($this->payment_date instanceof Carbon && $this->payment_date->copy()->startOfDay()->gte($dueDate)) {
                return 'paid';
            }
        }

        if ($dueDate->lt($asOfDate)) {
            return 'overdue';
        }

        return 'pending';
    }

    public function isPaymentOverdue(?Carbon $asOf = null): bool
    {
        return $this->derivedPaymentStatus($asOf) === 'overdue';
    }

    /**
     * Return the reporting status used by the admin boarding monitor.
     *
     * The stored booking status remains the source of truth. Approved rows
     * are projected into Active or Checked Out from their stay dates, while
     * future approved stays are shown as Pending until check-in.
     * Early-leave cancelled stays with physical occupancy (check_out > check_in)
     * are projected as Checked Out or Active during the occupied window.
     */
    public function monitoringStatus(?Carbon $asOf = null, ?Carbon $periodStart = null, ?Carbon $periodEnd = null): string
    {
        $asOfDate = ($asOf ?: now())->copy()->startOfDay();
        $storedStatus = strtolower((string) $this->status);

        if ($storedStatus === 'rejected') {
            return 'cancelled';
        }

        if ($storedStatus === 'cancelled') {
            if ($this->check_in && $this->check_out) {
                $checkIn = Carbon::parse($this->check_in)->startOfDay();
                $checkOut = Carbon::parse($this->check_out)->startOfDay();
                if ($checkOut->gt($checkIn)) {
                    if ($periodStart && $periodEnd) {
                        if ($checkIn->lte($periodEnd) && $checkOut->gte($periodStart)) {
                            return 'active';
                        }
                    } else {
                        if ($checkOut->lte($asOfDate)) {
                            return 'checked_out';
                        }
                        if ($checkIn->lte($asOfDate) && $checkOut->gt($asOfDate)) {
                            return 'active';
                        }
                    }
                }
            }
            return 'cancelled';
        }

        if ($storedStatus === 'pending') {
            return 'pending';
        }

        if ($storedStatus === 'approved') {
            $checkIn = $this->check_in ? Carbon::parse($this->check_in)->startOfDay() : null;
            $checkOut = $this->check_out ? Carbon::parse($this->check_out)->startOfDay() : null;

            if ($periodStart && $periodEnd) {
                if ($checkIn && $checkIn->gt($periodEnd)) {
                    return 'pending';
                }
                if ($checkIn && $checkIn->lte($periodEnd) && (!$checkOut || $checkOut->gte($periodStart))) {
                    if ($checkOut && $checkOut->lte($asOfDate) && $asOfDate->gte($periodEnd)) {
                        return 'checked_out';
                    }
                    return 'active';
                }
            }

            if ($checkOut && $checkOut->lte($asOfDate)) {
                return 'checked_out';
            }

            if ($checkIn && $checkIn->lte($asOfDate) && (!$checkOut || $checkOut->gt($asOfDate))) {
                return 'active';
            }
        }

        return 'pending';
    }

    public function scopeMonitoringStatus(
        Builder $query,
        string $status,
        ?Carbon $asOf = null,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null
    ): Builder {
        return app(\App\Services\BoardingMonitoringService::class)->applyStatusFilter(
            $query,
            $status,
            $asOf,
            $periodStart,
            $periodEnd
        );
    }
}
