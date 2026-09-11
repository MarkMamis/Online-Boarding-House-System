<?php

namespace App\Services\AI\Tools;

use App\Models\Property;
use App\Models\User;

class PropertyTools
{
    /**
     * Count properties in OBHS.
     */
    public function countProperties(User $user, array $args): array
    {
        $status = strtolower(trim((string) ($args['status'] ?? 'all')));
        $query = Property::query();

        if ($user->role === 'landlord') {
            $query->where('landlord_id', $user->id);
        } elseif ($user->role === 'student') {
            $query->visibleToAudience();
        }

        if ($status !== '' && $status !== 'all') {
            $query->where('approval_status', $status);
        }

        $total = (clone $query)->count();
        $approved = (clone $query)->where('approval_status', 'approved')->count();
        $pending = (clone $query)->where('approval_status', 'pending')->count();

        return [
            'total_properties' => $total,
            'approved_properties' => $approved,
            'pending_approval' => $pending,
            'scope' => $user->role === 'landlord' ? 'owned_properties' : ($user->role === 'student' ? 'public_visible' : 'system_wide'),
        ];
    }

    /**
     * Search properties by name, address, or price range.
     */
    public function searchProperties(User $user, array $args): array
    {
        $queryText = trim((string) ($args['query'] ?? ''));
        $maxPrice = !empty($args['max_price']) && is_numeric($args['max_price']) ? (float) $args['max_price'] : null;
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 15);

        $builder = Property::with(['landlord'])->withCount('rooms');

        if ($user->role === 'landlord') {
            $builder->where('landlord_id', $user->id);
        } elseif ($user->role === 'student') {
            $builder->visibleToAudience();
        }

        if ($queryText !== '') {
            $builder->where(function ($q) use ($queryText) {
                $q->where('name', 'like', "%{$queryText}%")
                  ->orWhere('address', 'like', "%{$queryText}%")
                  ->orWhere('description', 'like', "%{$queryText}%");
            });
        }

        if ($maxPrice !== null) {
            $builder->where('price_min', '<=', $maxPrice);
        }

        $results = $builder->latest()
            ->take($limit)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'address' => $p->address,
                'price_min' => (float) ($p->price_min ?: 0),
                'price_max' => (float) ($p->price_max ?: 0),
                'rooms_count' => $p->rooms_count,
                'rating' => (float) ($p->average_rating ?: 0),
                'approval_status' => $p->approval_status,
                'action_url' => $user->role === 'student' ? '/student/rooms?property=' . $p->id : '/admin/properties/' . $p->id,
            ])
            ->values()
            ->all();

        return [
            'matches_found' => count($results),
            'properties' => $results,
        ];
    }

    /**
     * Get detailed information on a single property.
     */
    public function getPropertyDetails(User $user, array $args): array
    {
        $id = (int) ($args['property_id'] ?? 0);
        $name = trim((string) ($args['property_name'] ?? ''));

        $query = Property::with(['landlord', 'rooms']);

        if ($user->role === 'landlord') {
            $query->where('landlord_id', $user->id);
        } elseif ($user->role === 'student') {
            $query->visibleToAudience();
        }

        if ($id > 0) {
            $property = $query->find($id);
        } elseif ($name !== '') {
            $property = $query->where('name', 'like', "%{$name}%")->first();
        } else {
            return ['error' => 'Please provide property_id or property_name.'];
        }

        if (!$property) {
            return ['error' => 'Property not found or not accessible.'];
        }

        $totalCapacity = 0;
        $totalAvailableSlots = 0;
        $totalOccupiedSlots = 0;

        foreach ($property->rooms as $room) {
            $snap = $room->occupancySnapshot();
            $totalCapacity += (int) $room->capacity;
            $totalAvailableSlots += (int) ($snap['available_slots'] ?? 0);
            $totalOccupiedSlots += (int) ($snap['occupied_slots'] ?? 0);
        }

        return [
            'id' => $property->id,
            'name' => $property->name,
            'address' => $property->address,
            'description' => $property->description,
            'approval_status' => $property->approval_status,
            'price_min' => (float) ($property->price_min ?: 0),
            'price_max' => (float) ($property->price_max ?: 0),
            'rating' => (float) ($property->average_rating ?: 0),
            'total_rooms' => $property->rooms->count(),
            'total_capacity' => $totalCapacity,
            'available_slots' => $totalAvailableSlots,
            'occupied_slots' => $totalOccupiedSlots,
            'inclusions' => (array) ($property->building_inclusions ?? []),
            'house_rules' => (array) ($property->house_rules ?? []),
            'landlord_name' => $property->landlord?->name ?? 'N/A',
        ];
    }

    /**
     * Get occupancy statistics for a property or across properties.
     */
    public function getPropertyOccupancy(User $user, array $args): array
    {
        $propertyId = (int) ($args['property_id'] ?? 0);

        if ($propertyId > 0) {
            $property = Property::with('rooms')->find($propertyId);
            if (!$property) {
                return ['error' => 'Property not found.'];
            }

            if ($user->role === 'landlord' && (int) $property->landlord_id !== (int) $user->id) {
                return ['error' => 'Unauthorized. This property does not belong to you.'];
            }

            $totalCapacity = 0;
            $occupiedSlots = 0;
            $availableSlots = 0;

            foreach ($property->rooms as $room) {
                $snap = $room->occupancySnapshot();
                $totalCapacity += (int) $room->capacity;
                $occupiedSlots += (int) ($snap['occupied_slots'] ?? 0);
                $availableSlots += (int) ($snap['available_slots'] ?? 0);
            }

            $rate = $totalCapacity > 0 ? round(($occupiedSlots / $totalCapacity) * 100, 1) : 0;

            return [
                'property_id' => $property->id,
                'property_name' => $property->name,
                'total_rooms' => $property->rooms->count(),
                'total_capacity' => $totalCapacity,
                'occupied_slots' => $occupiedSlots,
                'available_slots' => $availableSlots,
                'occupancy_rate_percent' => $rate,
            ];
        }

        // Aggregate across all accessible properties (Admin or Landlord)
        $query = Property::with('rooms');
        if ($user->role === 'landlord') {
            $query->where('landlord_id', $user->id);
        }

        $properties = $query->get();
        $rankings = [];

        foreach ($properties as $prop) {
            $cap = 0;
            $occ = 0;
            $avail = 0;
            foreach ($prop->rooms as $room) {
                $snap = $room->occupancySnapshot();
                $cap += (int) $room->capacity;
                $occ += (int) ($snap['occupied_slots'] ?? 0);
                $avail += (int) ($snap['available_slots'] ?? 0);
            }
            $pct = $cap > 0 ? round(($occ / $cap) * 100, 1) : 0;
            $rankings[] = [
                'property_id' => $prop->id,
                'property_name' => $prop->name,
                'capacity' => $cap,
                'occupied' => $occ,
                'available' => $avail,
                'occupancy_rate_percent' => $pct,
            ];
        }

        // Sort descending by occupancy rate
        usort($rankings, fn ($a, $b) => $b['occupancy_rate_percent'] <=> $a['occupancy_rate_percent']);

        $highestOccupancy = $rankings[0] ?? null;
        $lowestOccupancy = !empty($rankings) ? end($rankings) : null;

        return [
            'total_properties_analyzed' => count($rankings),
            'highest_occupancy_property' => $highestOccupancy,
            'lowest_occupancy_property' => $lowestOccupancy,
            'top_properties' => array_slice($rankings, 0, 5),
        ];
    }
}
