<?php

namespace App\Services\AI\Tools;

use App\Models\Property;
use App\Models\Room;
use App\Models\User;

class RoomTools
{
    /**
     * Search available rooms with filters.
     */
    public function searchAvailableRooms(User $user, array $args): array
    {
        $maxPrice = !empty($args['max_price']) && is_numeric($args['max_price']) ? (float) $args['max_price'] : null;
        $occupancyMode = strtolower(trim((string) ($args['occupancy_mode'] ?? 'any')));
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 10);

        $query = Room::with('property')
            ->where('status', '!=', 'maintenance')
            ->whereHas('property', function ($q) {
                $q->visibleToAudience();
            });

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        $rooms = $query->orderBy('price')->get();
        $available = [];

        foreach ($rooms as $room) {
            if (!$room->hasAvailableSlots()) {
                continue;
            }

            if ($occupancyMode !== 'any' && in_array($occupancyMode, ['solo', 'shared'], true)) {
                if (!$room->supportsOccupancyMode($occupancyMode)) {
                    continue;
                }
            }

            $price = (float) $room->price;
            $effectivePerBed = $room->effectivePricePerBed();
            $effectivePerRoom = $room->effectivePricePerRoom();

            $available[] = [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'property_name' => $room->property?->name ?? 'Unknown',
                'property_address' => $room->property?->address ?? 'N/A',
                'price' => $price,
                'price_per_bed' => $effectivePerBed,
                'price_per_room' => $effectivePerRoom,
                'pricing_model' => $room->resolvePricingModel(),
                'capacity' => (int) $room->capacity,
                'slots_available' => $room->getAvailableSlots(),
                'action_url' => '/student/rooms/' . $room->id,
            ];

            if (count($available) >= $limit) {
                break;
            }
        }

        return [
            'total_found' => count($available),
            'rooms' => $available,
        ];
    }

    /**
     * Get details for a specific room.
     */
    public function getRoomDetails(User $user, array $args): array
    {
        $roomId = (int) ($args['room_id'] ?? 0);
        if ($roomId <= 0) {
            return ['error' => 'Please provide a valid room_id.'];
        }

        $room = Room::with('property')->find($roomId);
        if (!$room) {
            return ['error' => 'Room not found.'];
        }

        $snap = $room->occupancySnapshot();

        return [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
            'property_name' => $room->property?->name ?? 'Unknown',
            'property_address' => $room->property?->address ?? 'N/A',
            'status' => $room->status,
            'price' => (float) $room->price,
            'price_per_bed' => $room->effectivePricePerBed(),
            'price_per_room' => $room->effectivePricePerRoom(),
            'pricing_model' => $room->resolvePricingModel(),
            'capacity' => (int) $room->capacity,
            'slots_available' => (int) ($snap['available_slots'] ?? 0),
            'occupied_slots' => (int) ($snap['occupied_slots'] ?? 0),
            'inclusions' => (array) ($room->inclusions ?? []),
            'action_url' => '/student/rooms/' . $room->id,
        ];
    }

    /**
     * Get cheapest available rooms.
     */
    public function getCheapestAvailableRooms(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 3), 1), 5);

        $rooms = Room::with('property')
            ->where('status', '!=', 'maintenance')
            ->whereHas('property', function ($q) {
                $q->visibleToAudience();
            })
            ->orderBy('price')
            ->get();

        $cheapest = [];
        foreach ($rooms as $room) {
            if (!$room->hasAvailableSlots()) {
                continue;
            }

            $cheapest[] = [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'property_name' => $room->property?->name ?? 'Unknown',
                'property_address' => $room->property?->address ?? 'N/A',
                'price' => (float) $room->price,
                'capacity' => (int) $room->capacity,
                'slots_available' => $room->getAvailableSlots(),
                'action_url' => '/student/rooms/' . $room->id,
            ];

            if (count($cheapest) >= $limit) {
                break;
            }
        }

        if (empty($cheapest)) {
            return [
                'has_available' => false,
                'message' => 'No available rooms found at this moment.',
                'action_url' => '/student/rooms',
            ];
        }

        return [
            'has_available' => true,
            'cheapest_room' => $cheapest[0],
            'top_cheapest' => $cheapest,
        ];
    }

    /**
     * Get nearest available rooms based on geographic coordinates.
     */
    public function getNearestAvailableRooms(User $user, array $args): array
    {
        $lat = isset($args['latitude']) ? (float) $args['latitude'] : null;
        $lng = isset($args['longitude']) ? (float) $args['longitude'] : null;
        $limit = min(max((int) ($args['limit'] ?? 3), 1), 5);

        if ($lat === null || $lng === null) {
            return [
                'requires_geo' => true,
                'message' => 'Please share your location (latitude and longitude) to find the nearest boarding houses.',
            ];
        }

        $rooms = Room::with('property')
            ->where('status', '!=', 'maintenance')
            ->whereHas('property', function ($q) {
                $q->visibleToAudience()
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude');
            })
            ->get();

        $candidates = [];
        foreach ($rooms as $room) {
            if (!$room->hasAvailableSlots()) {
                continue;
            }

            $pLat = (float) $room->property->latitude;
            $pLng = (float) $room->property->longitude;
            $km = $this->haversineKm($lat, $lng, $pLat, $pLng);

            $candidates[] = [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'property_name' => $room->property->name,
                'property_address' => $room->property->address,
                'price' => (float) $room->price,
                'slots_available' => $room->getAvailableSlots(),
                'distance_km' => round($km, 2),
                'action_url' => '/student/rooms/' . $room->id,
            ];
        }

        usort($candidates, fn ($a, $b) => $a['distance_km'] <=> $b['distance_km']);
        $nearest = array_slice($candidates, 0, $limit);

        return [
            'nearest_rooms' => $nearest,
            'user_location' => ['latitude' => $lat, 'longitude' => $lng],
        ];
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
