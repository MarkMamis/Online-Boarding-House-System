<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;

class RoomOccupancyRules
{
    public function snapshot(Room $room, ?string $asOfDate = null): array
    {
        $date = $asOfDate ?: now()->toDateString();
        $capacity = max(1, (int) $room->capacity);
        $pricingModel = $room->resolvePricingModel();

        $activeApprovedQuery = Booking::query()
            ->where('room_id', $room->id)
            ->where('status', 'approved')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date);

        $activeOccupantsCount = (int) (clone $activeApprovedQuery)->count();
        $hasActiveSoloOccupancy = (clone $activeApprovedQuery)
            ->where(function ($query) {
                $query->where('occupancy_mode', 'solo')
                    ->orWhereNull('occupancy_mode');
            })
            ->exists();

        $hasActiveSharedOccupancy = (clone $activeApprovedQuery)
            ->where('occupancy_mode', 'shared')
            ->exists();

        $availableSlots = match ($pricingModel) {
            Room::PRICING_MODEL_PER_ROOM => $activeOccupantsCount > 0 ? 0 : $capacity,
            Room::PRICING_MODEL_PER_BED => max(0, $capacity - $activeOccupantsCount),
            default => $hasActiveSoloOccupancy
                ? 0
                : max(0, $capacity - $activeOccupantsCount),
        };

        $occupiedSlots = max(0, min($capacity, $capacity - $availableSlots));

        $effectiveStatus = (string) $room->status === 'maintenance'
            ? 'maintenance'
            : ($availableSlots > 0 ? 'available' : 'occupied');

        $hybridListingMode = null;
        if ($pricingModel === Room::PRICING_MODEL_HYBRID) {
            if ($hasActiveSoloOccupancy) {
                $hybridListingMode = Room::PRICING_MODEL_PER_ROOM;
            } elseif ($hasActiveSharedOccupancy || $activeOccupantsCount > 0) {
                $hybridListingMode = Room::PRICING_MODEL_PER_BED;
            } else {
                $hybridListingMode = 'both';
            }
        }

        return [
            'as_of_date' => $date,
            'capacity' => $capacity,
            'active_occupants_count' => $activeOccupantsCount,
            'has_active_solo_occupancy' => $hasActiveSoloOccupancy,
            'has_active_shared_occupancy' => $hasActiveSharedOccupancy,
            'available_slots' => $availableSlots,
            'occupied_slots' => $occupiedSlots,
            'status' => $effectiveStatus,
            'pricing_model' => $pricingModel,
            'hybrid_listing_mode' => $hybridListingMode,
        ];
    }

    public function sync(Room $room, ?string $asOfDate = null): array
    {
        $snapshot = $this->snapshot($room, $asOfDate);

        $nextSlots = (int) ($snapshot['available_slots'] ?? 0);
        $nextStatus = (string) ($snapshot['status'] ?? 'available');

        if ((string) $room->status === 'maintenance') {
            $room->fill([
                'slots_available' => 0,
            ]);
        } else {
            $room->fill([
                'slots_available' => $nextSlots,
                'status' => $nextStatus,
            ]);
        }

        if ($room->isDirty(['slots_available', 'status'])) {
            $room->save();
        }

        return $snapshot;
    }
}
