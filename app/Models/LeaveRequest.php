<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'student_id',
        'landlord_id',
        'status',
        'leave_date',
        'reason',
        'landlord_response',
        'responded_at',
    ];

    protected $casts = [
        'leave_date' => 'date',
        'responded_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }
}
