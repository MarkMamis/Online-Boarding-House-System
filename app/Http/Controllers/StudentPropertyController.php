<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StudentPropertyController extends Controller
{
    public function show(Property $property)
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }

        if (($property->approval_status ?? 'pending') !== 'approved') {
            abort(404);
        }
        // Load landlord and rooms
        $property->load(['landlord:id,full_name', 'rooms' => function($q){
            $q->orderBy('room_number');
        }])->loadCount([
            'rooms as rooms_total_live',
            'rooms as rooms_available_live' => function($q){ $q->where('status','available'); },
        ]);

        return view('student.properties.show', compact('property'));
    }

    public function map()
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }
        return view('student.properties.map');
    }

    public function mapData(): JsonResponse
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }

        $properties = Property::query()
            ->where('approval_status', 'approved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount([
                'rooms as available_rooms' => function ($q) {
                    $q->where('status', 'available');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'address', 'latitude', 'longitude']);

        $payload = $properties->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'address' => $p->address,
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
                'available_rooms' => (int) ($p->available_rooms ?? 0),
            ];
        })->values();

        return response()->json([
            'properties' => $payload,
        ]);
    }

    public function roomsData(Property $property): JsonResponse
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }

        if (($property->approval_status ?? 'pending') !== 'approved') {
            abort(404);
        }

        $property->load(['rooms' => function ($q) {
            $q->orderBy('room_number');
        }]);

        $rooms = $property->rooms->map(function ($r) {
            return [
                'id' => $r->id,
                'room_number' => $r->room_number,
                'capacity' => (int) ($r->capacity ?? 0),
                'price' => (float) ($r->price ?? 0),
                'status' => (string) ($r->status ?? ''),
            ];
        })->values();

        return response()->json([
            'property' => [
                'id' => $property->id,
                'name' => $property->name,
                'address' => $property->address,
            ],
            'rooms' => $rooms,
        ]);
    }
}
