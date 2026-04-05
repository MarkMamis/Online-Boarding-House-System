<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StudentPropertyController extends Controller
{
    public function show(Property $property)
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }

        if (!Property::query()->visibleToAudience()->whereKey($property->id)->exists()) {
            abort(404);
        }
        // Load landlord and rooms
        $property->load(['landlord:id,full_name', 'rooms' => function($q){
            $q->orderBy('room_number');
        }])->loadCount([
            'rooms as rooms_total_live',
            'rooms as rooms_available_live' => function($q){
                $q->where('status','available')->where('slots_available', '>', 0);
            },
        ]);

        return view('student.properties.show', compact('property'));
    }

    public function map(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }

        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $minCapacity = $request->query('capacity');
        $search = $request->query('q');
        $supportsBuildingInclusions = Schema::hasColumn('properties', 'building_inclusions');
        $amenityOptions = (array) config('property_amenities.flat', []);
        $amenity = trim((string) $request->query('amenity', ''));
        if (!$supportsBuildingInclusions || ($amenity !== '' && !array_key_exists($amenity, $amenityOptions))) {
            $amenity = '';
        }

        $allProperties = Property::with(['landlord:id,full_name'])
            ->visibleToAudience()
            ->withCount([
                'rooms as rooms_total_live',
                'rooms as rooms_available_live' => function ($q) {
                    $q->where('status', 'available')->where('slots_available', '>', 0);
                },
            ])
            ->when($search && $search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%$search%")
                       ->orWhere('address', 'like', "%$search%");
                });
            })
            ->when($amenity !== '', function ($q) use ($amenity) {
                $q->whereJsonContains('building_inclusions', $amenity);
            })
            ->orderBy('name')
            ->get();

        return view('student.properties.map', compact(
            'allProperties',
            'minPrice',
            'maxPrice',
            'minCapacity',
            'search',
            'amenity',
            'amenityOptions'
        ));
    }

    public function mapData(Request $request): JsonResponse
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }

        $search = trim((string) $request->query('q', ''));
        $supportsBuildingInclusions = Schema::hasColumn('properties', 'building_inclusions');
        $amenityOptions = (array) config('property_amenities.flat', []);
        $amenity = trim((string) $request->query('amenity', ''));
        if (!$supportsBuildingInclusions || ($amenity !== '' && !array_key_exists($amenity, $amenityOptions))) {
            $amenity = '';
        }

        $selectColumns = ['id', 'name', 'address', 'latitude', 'longitude', 'image_path', 'price_min', 'price_max'];
        if ($supportsBuildingInclusions) {
            $selectColumns[] = 'building_inclusions';
        }

        $properties = Property::query()
            ->visibleToAudience()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($amenity !== '', function ($q) use ($amenity) {
                $q->whereJsonContains('building_inclusions', $amenity);
            })
            ->withCount([
                'rooms as available_rooms' => function ($q) {
                    $q->where('status', 'available')->where('slots_available', '>', 0);
                },
            ])
            ->orderBy('name')
            ->get($selectColumns);

        $payload = $properties->map(function ($p) use ($amenityOptions) {
            $imagePath = ltrim((string) ($p->image_path ?? ''), '/');
            $imageExists = $imagePath !== '' && (
                Storage::disk('public')->exists($imagePath)
                || file_exists(public_path('storage/' . $imagePath))
            );

            $inclusions = collect((array) ($p->building_inclusions ?? []))
                ->map(fn ($key) => $amenityOptions[$key] ?? null)
                ->filter()
                ->values()
                ->all();

            return [
                'id' => $p->id,
                'name' => $p->name,
                'address' => $p->address,
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
                'available_rooms' => (int) ($p->available_rooms ?? 0),
                'price_min' => $p->price_min !== null ? (float) $p->price_min : null,
                'price_max' => $p->price_max !== null ? (float) $p->price_max : null,
                'image_url' => $imageExists ? asset('storage/' . $imagePath) : null,
                'inclusions' => $inclusions,
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

        if (!Property::query()->visibleToAudience()->whereKey($property->id)->exists()) {
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
