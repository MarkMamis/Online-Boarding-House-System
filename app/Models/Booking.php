<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}
