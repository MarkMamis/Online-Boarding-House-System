<?php

namespace App\Models;

use App\Services\RoomOccupancyRules;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected array $occupancySnapshotCache = [];

    public const PRICING_MODEL_PER_ROOM = 'per_room';
    public const PRICING_MODEL_PER_BED = 'per_bed';
    public const PRICING_MODEL_HYBRID = 'hybrid';

    protected $fillable = [
        'property_id',
        'room_number',
        'capacity',
        'slots_available',
        'price',
        'pricing_model',
        'price_per_room',
        'price_per_bed',
        'status',
        'image_path',
        'inclusions',
        'requires_advance_payment',
        'maintenance_reason',
        'maintenance_date',
    ];

    protected $casts = [
        'maintenance_date' => 'datetime',
        'slots_available' => 'integer',
        'price' => 'float',
        'price_per_room' => 'float',
        'price_per_bed' => 'float',
        'requires_advance_payment' => 'boolean',
    ];

    public function resolvePricingModel(): string
    {
        $value = strtolower((string) ($this->pricing_model ?: self::PRICING_MODEL_HYBRID));
        if (!in_array($value, [self::PRICING_MODEL_PER_ROOM, self::PRICING_MODEL_PER_BED, self::PRICING_MODEL_HYBRID], true)) {
            return self::PRICING_MODEL_HYBRID;
        }

        return $value;
    }

    public function allowedOccupancyModes(): array
    {
        return match ($this->resolvePricingModel()) {
            self::PRICING_MODEL_PER_ROOM => ['solo'],
            self::PRICING_MODEL_PER_BED => ['shared'],
            default => ['solo', 'shared'],
        };
    }

    public function supportsOccupancyMode(string $occupancyMode): bool
    {
        return in_array(strtolower($occupancyMode), $this->allowedOccupancyModes(), true);
    }

    public function effectivePricePerRoom(): float
    {
        $perRoom = (float) ($this->price_per_room ?? 0);
        if ($perRoom > 0) {
            return $perRoom;
        }

        $fallback = (float) ($this->price ?? 0);
        if ($fallback > 0) {
            return $fallback;
        }

        $perBed = (float) ($this->price_per_bed ?? 0);
        return round($perBed * max(1, (int) $this->capacity), 2);
    }

    public function effectivePricePerBed(): float
    {
        $perBed = (float) ($this->price_per_bed ?? 0);
        if ($perBed > 0) {
            return $perBed;
        }

        $capacity = max(1, (int) $this->capacity);
        $perRoom = (float) ($this->price_per_room ?? 0);
        if ($perRoom > 0) {
            return round($perRoom / $capacity, 2);
        }

        $fallback = (float) ($this->price ?? 0);
        return $fallback > 0 ? round($fallback / $capacity, 2) : 0.0;
    }

    public function resolveMonthlyRentForOccupancyMode(string $occupancyMode): float
    {
        $mode = strtolower($occupancyMode);
        if ($mode === 'shared') {
            return $this->effectivePricePerBed();
        }

        return $this->effectivePricePerRoom();
    }

    public function normalizePricingForStorage(): array
    {
        $pricingModel = $this->resolvePricingModel();
        $pricePerRoom = $this->effectivePricePerRoom();
        $pricePerBed = $this->effectivePricePerBed();

        $basePrice = match ($pricingModel) {
            self::PRICING_MODEL_PER_ROOM => $pricePerRoom,
            self::PRICING_MODEL_PER_BED => $pricePerBed,
            default => min($pricePerRoom, $pricePerBed),
        };

        return [
            'pricing_model' => $pricingModel,
            'price_per_room' => $pricePerRoom,
            'price_per_bed' => $pricePerBed,
            'price' => $basePrice,
        ];
    }

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
        return (int) ($this->occupancySnapshot()['active_occupants_count'] ?? 0);
    }

    public function occupancySnapshot(?string $asOfDate = null, bool $fresh = false): array
    {
        $date = $asOfDate ?: now()->toDateString();
        if (!$fresh && array_key_exists($date, $this->occupancySnapshotCache)) {
            return $this->occupancySnapshotCache[$date];
        }

        $snapshot = app(RoomOccupancyRules::class)->snapshot($this, $date);
        $this->occupancySnapshotCache[$date] = $snapshot;

        return $snapshot;
    }

    public function syncAvailabilitySnapshot(?string $asOfDate = null): array
    {
        $snapshot = app(RoomOccupancyRules::class)->sync($this, $asOfDate);
        $date = (string) ($snapshot['as_of_date'] ?? ($asOfDate ?: now()->toDateString()));
        $this->occupancySnapshotCache[$date] = $snapshot;

        return $snapshot;
    }

    public function resolveListingPricingMode(?string $asOfDate = null): string
    {
        $pricingModel = $this->resolvePricingModel();
        if ($pricingModel !== self::PRICING_MODEL_HYBRID) {
            return $pricingModel;
        }

        $hybridMode = (string) ($this->occupancySnapshot($asOfDate)['hybrid_listing_mode'] ?? 'both');
        if (in_array($hybridMode, [self::PRICING_MODEL_PER_ROOM, self::PRICING_MODEL_PER_BED, 'both'], true)) {
            return $hybridMode;
        }

        return 'both';
    }

    /**
     * Get number of available slots in this room
     */
    public function getAvailableSlots(): int
    {
        if ((string) $this->status === 'maintenance') {
            return 0;
        }

        return (int) ($this->occupancySnapshot()['available_slots'] ?? 0);
    }

    /**
     * Check if room has available slots (truly available for booking)
     */
    public function hasAvailableSlots(): bool
    {
        return (string) $this->status !== 'maintenance' && $this->getAvailableSlots() > 0;
    }

    /**
     * Get occupancy display (e.g., "2/4")
     */
    public function getOccupancyDisplay(): string
    {
        $occupied = (int) ($this->occupancySnapshot()['occupied_slots'] ?? 0);
        return "{$occupied}/{$this->capacity}";
    }
}
