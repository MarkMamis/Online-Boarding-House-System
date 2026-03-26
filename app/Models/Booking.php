<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'payment_date' => 'datetime',
        'include_advance_payment' => 'boolean',
        'monthly_rent_amount' => 'decimal:2',
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

    public function getDurationInDays()
    {
        return $this->check_in->diffInDays($this->check_out);
    }
}
