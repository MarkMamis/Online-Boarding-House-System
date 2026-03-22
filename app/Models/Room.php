<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'room_number',
        'capacity',
        'slots_available',
        'price',
        'status',
        'image_path',
        'inclusions',
        'maintenance_reason',
        'maintenance_date',
    ];

    protected $casts = [
        'maintenance_date' => 'datetime',
        'slots_available' => 'integer',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function roomImages()
    {
        return $this->hasMany(RoomImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function feedbacks()
    {
        return $this->hasMany(\App\Models\RoomFeedback::class)->latest();
    }

    /**
     * Count actual onboarded tenants in this room (active bookings with completed onboarding)
     */
    public function getOnboardedTenantsCount(): int
    {
        return (int) \DB::table('bookings')
            ->join('tenant_onboardings', 'tenant_onboardings.booking_id', '=', 'bookings.id')
            ->where('bookings.room_id', $this->id)
            ->where('bookings.status', 'approved')
            ->where(function ($q) {
                $q->whereNull('bookings.check_out')
                  ->orWhereDate('bookings.check_out', '>=', now()->toDateString());
            })
            ->distinct('bookings.student_id')
            ->count('bookings.student_id');
    }

    /**
     * Get number of available slots in this room
     */
    public function getAvailableSlots(): int
    {
        if ($this->slots_available !== null) {
            return max(0, (int) $this->slots_available);
        }

        $onboarded = $this->getOnboardedTenantsCount();
        return max(0, (int) $this->capacity - $onboarded);
    }

    /**
     * Check if room has available slots (truly available for booking)
     */
    public function hasAvailableSlots(): bool
    {
        return $this->getAvailableSlots() > 0;
    }

    /**
     * Get occupancy display (e.g., "2/4")
     */
    public function getOccupancyDisplay(): string
    {
        $available = $this->getAvailableSlots();
        $occupied = max(0, (int) $this->capacity - $available);
        return "{$occupied}/{$this->capacity}";
    }
}
