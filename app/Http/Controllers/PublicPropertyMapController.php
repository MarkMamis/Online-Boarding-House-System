<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PublicPropertyMapController extends Controller
{
    public function index(Request $request)
    {
        $supportsBuildingInclusions = Schema::hasColumn('properties', 'building_inclusions');
        $amenityOptions = (array) config('property_amenities.flat', []);

        $search = trim((string) $request->query('q', ''));
        $selectedAmenities = $this->parseSelectedAmenities($request, $supportsBuildingInclusions, $amenityOptions);

        $minRatingInput = $request->query('min_rating');
        $minRating = is_numeric($minRatingInput) ? max(0, min(5, (float) $minRatingInput)) : null;

        $priceBounds = $this->getPriceBounds();
        [$minPrice, $maxPrice] = $this->normalizePriceRange(
            $request->query('min_price'),
            $request->query('max_price'),
            $priceBounds['min'],
            $priceBounds['max']
        );

        $selectColumns = [
            'id',
            'name',
            'address',
            'description',
            'latitude',
            'longitude',
            'image_path',
            'average_rating',
            'ratings_count',
            'price_min',
            'price_max',
            'created_at',
        ];
        if ($supportsBuildingInclusions) {
            $selectColumns[] = 'building_inclusions';
        }

        $properties = Property::query()
            ->visibleToAudience()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when(!empty($selectedAmenities), function ($q) use ($selectedAmenities) {
                $q->where(function ($qq) use ($selectedAmenities) {
                    foreach ($selectedAmenities as $amenityKey) {
                        $qq->orWhereJsonContains('building_inclusions', $amenityKey);
                    }
                });
            })
            ->when($minRating !== null, function ($q) use ($minRating) {
                $q->where('average_rating', '>=', $minRating);
            })
            ->when($minPrice !== null || $maxPrice !== null, function ($q) use ($minPrice, $maxPrice) {
                $q->whereHas('rooms', function ($roomQuery) use ($minPrice, $maxPrice) {
                    if ($minPrice !== null) {
                        $roomQuery->where('price', '>=', $minPrice);
                    }
                    if ($maxPrice !== null) {
                        $roomQuery->where('price', '<=', $maxPrice);
                    }
                });
            })
            ->withCount([
                'rooms as total_rooms_count',
                'rooms as available_rooms_count' => function ($q) {
                    $q->where('status', 'available')->where('slots_available', '>', 0);
                },
            ])
            ->withMin('rooms as rooms_price_min', 'price')
            ->withMax('rooms as rooms_price_max', 'price')
            ->orderByDesc('average_rating')
            ->orderByDesc('ratings_count')
            ->orderBy('name')
            ->get($selectColumns);

        $publicRooms = Room::query()
            ->with([
                'property:id,name,address,landlord_id,image_path,average_rating,ratings_count,latitude,longitude',
                'property.landlord:id,full_name',
            ])
            ->withAvg('feedbacks', 'rating')
            ->withCount('feedbacks')
            ->where('status', '!=', 'maintenance')
            ->whereHas('property', function ($q) use ($search, $selectedAmenities, $minRating) {
                $q->visibleToAudience()
                    ->when($search !== '', function ($propertyQuery) use ($search) {
                        $propertyQuery->where(function ($qq) use ($search) {
                            $qq->where('name', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%");
                        });
                    })
                    ->when(!empty($selectedAmenities), function ($propertyQuery) use ($selectedAmenities) {
                        $propertyQuery->where(function ($qq) use ($selectedAmenities) {
                            foreach ($selectedAmenities as $amenityKey) {
                                $qq->orWhereJsonContains('building_inclusions', $amenityKey);
                            }
                        });
                    })
                    ->when($minRating !== null, function ($propertyQuery) use ($minRating) {
                        $propertyQuery->where('average_rating', '>=', $minRating);
                    });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('room_number', 'like', "%{$search}%")
                        ->orWhereHas('property', function ($propertyQuery) use ($search) {
                            $propertyQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%");
                        });
                });
            })
            ->when($minPrice !== null, function ($q) use ($minPrice) {
                $q->where('price', '>=', $minPrice);
            })
            ->when($maxPrice !== null, function ($q) use ($maxPrice) {
                $q->where('price', '<=', $maxPrice);
            })
            ->orderBy('price')
            ->orderBy('room_number')
            ->get();

        $mappedCount = (int) $properties
            ->filter(fn ($property) => $property->latitude !== null && $property->longitude !== null)
            ->count();

        $sliderMinValue = $minPrice !== null ? (int) round($minPrice) : $priceBounds['min'];
        $sliderMaxValue = $maxPrice !== null ? (int) round($maxPrice) : $priceBounds['max'];

        return view('public.property_map', compact(
            'properties',
            'publicRooms',
            'mappedCount',
            'search',
            'selectedAmenities',
            'amenityOptions',
            'minRating',
            'minPrice',
            'maxPrice',
            'priceBounds',
            'sliderMinValue',
            'sliderMaxValue'
        ));
    }

    public function suggestions(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        $rawSuggestions = Property::query()
            ->visibleToAudience()
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', "%{$term}%")
                        ->orWhere('address', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['name', 'address']);

        $suggestions = $rawSuggestions
            ->flatMap(function ($property) {
                $name = trim((string) ($property->name ?? ''));
                $address = trim((string) ($property->address ?? ''));

                $items = [];
                if ($name !== '') {
                    $items[] = $name;
                }
                if ($address !== '') {
                    $items[] = $address;
                }
                if ($name !== '' && $address !== '') {
                    $items[] = $name . ' - ' . $address;
                }

                return $items;
            })
            ->map(fn ($entry) => trim((string) $entry))
            ->filter(fn ($entry) => $entry !== '')
            ->unique()
            ->values()
            ->take(12)
            ->all();

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    public function propertyRooms(Property $property)
    {
        if (!Property::query()->visibleToAudience()->whereKey($property->id)->exists()) {
            abort(404);
        }

        $property->load([
            'landlord:id,full_name,address,profile_image_path,created_at',
            'landlord.properties' => function ($query) {
                $query->visibleToAudience()
                    ->select('id', 'landlord_id', 'name')
                    ->orderBy('name');
            },
            'landlord.landlordProfile:id,user_id,contact_number,boarding_house_name,about',
        ]);

        $rooms = Room::query()
            ->where('property_id', $property->id)
            ->where('status', '!=', 'maintenance')
            ->withAvg('feedbacks', 'rating')
            ->withCount('feedbacks')
            ->with([
                'feedbacks' => function ($query) {
                    $query->latest()->with('user:id,full_name');
                },
            ])
            ->orderBy('price')
            ->orderBy('room_number')
            ->get();

        return view('public.property_rooms', compact('property', 'rooms'));
    }

    public function mapData(Request $request): JsonResponse
    {
        $supportsBuildingInclusions = Schema::hasColumn('properties', 'building_inclusions');
        $amenityOptions = (array) config('property_amenities.flat', []);

        $search = trim((string) $request->query('q', ''));
        $selectedAmenities = $this->parseSelectedAmenities($request, $supportsBuildingInclusions, $amenityOptions);

        $minRatingInput = $request->query('min_rating');
        $minRating = is_numeric($minRatingInput) ? max(0, min(5, (float) $minRatingInput)) : null;

        $priceBounds = $this->getPriceBounds();
        [$minPrice, $maxPrice] = $this->normalizePriceRange(
            $request->query('min_price'),
            $request->query('max_price'),
            $priceBounds['min'],
            $priceBounds['max']
        );

        $selectColumns = [
            'id',
            'name',
            'address',
            'latitude',
            'longitude',
            'image_path',
            'average_rating',
            'ratings_count',
            'price_min',
            'price_max',
        ];
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
            ->when(!empty($selectedAmenities), function ($q) use ($selectedAmenities) {
                $q->where(function ($qq) use ($selectedAmenities) {
                    foreach ($selectedAmenities as $amenityKey) {
                        $qq->orWhereJsonContains('building_inclusions', $amenityKey);
                    }
                });
            })
            ->when($minRating !== null, function ($q) use ($minRating) {
                $q->where('average_rating', '>=', $minRating);
            })
            ->when($minPrice !== null || $maxPrice !== null, function ($q) use ($minPrice, $maxPrice) {
                $q->whereHas('rooms', function ($roomQuery) use ($minPrice, $maxPrice) {
                    if ($minPrice !== null) {
                        $roomQuery->where('price', '>=', $minPrice);
                    }
                    if ($maxPrice !== null) {
                        $roomQuery->where('price', '<=', $maxPrice);
                    }
                });
            })
            ->withCount([
                'rooms as available_rooms_count' => function ($q) {
                    $q->where('status', 'available')->where('slots_available', '>', 0);
                },
            ])
            ->withMin('rooms as rooms_price_min', 'price')
            ->withMax('rooms as rooms_price_max', 'price')
            ->orderByDesc('average_rating')
            ->orderByDesc('ratings_count')
            ->orderBy('name')
            ->get($selectColumns);

        $payload = $properties->map(function ($property) use ($amenityOptions) {
            $imagePath = ltrim((string) ($property->image_path ?? ''), '/');
            $imageExists = $imagePath !== '' && (
                Storage::disk('public')->exists($imagePath)
                || file_exists(public_path('storage/' . $imagePath))
            );

            $inclusions = collect((array) ($property->building_inclusions ?? []))
                ->map(fn ($key) => $amenityOptions[$key] ?? null)
                ->filter()
                ->take(5)
                ->values()
                ->all();

            $priceMin = $property->rooms_price_min !== null
                ? (float) $property->rooms_price_min
                : ($property->price_min !== null ? (float) $property->price_min : null);
            $priceMax = $property->rooms_price_max !== null
                ? (float) $property->rooms_price_max
                : ($property->price_max !== null ? (float) $property->price_max : null);

            return [
                'id' => $property->id,
                'name' => $property->name,
                'address' => $property->address,
                'lat' => (float) $property->latitude,
                'lng' => (float) $property->longitude,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'rating' => $property->average_rating !== null ? round((float) $property->average_rating, 1) : null,
                'ratings_count' => (int) ($property->ratings_count ?? 0),
                'available_rooms_count' => (int) ($property->available_rooms_count ?? 0),
                'inclusions' => $inclusions,
                'image_url' => $imageExists ? asset('storage/' . $imagePath) : null,
                'rooms_url' => route('public.properties.rooms', $property->id),
            ];
        })->values();

        return response()->json([
            'properties' => $payload,
        ]);
    }

    private function getPriceBounds(): array
    {
        $aggregates = Room::query()
            ->where('price', '>=', 0)
            ->whereHas('property', function ($query) {
                $query->visibleToAudience();
            })
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        $min = max(0, (int) floor((float) ($aggregates?->min_price ?? 0)));
        $max = max(0, (int) ceil((float) ($aggregates?->max_price ?? 0)));

        if ($max <= $min) {
            $max = max($min + 1000, 5000);
        }

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    private function parseSelectedAmenities(Request $request, bool $supportsBuildingInclusions, array $amenityOptions): array
    {
        if (!$supportsBuildingInclusions) {
            return [];
        }

        $amenitiesInput = $request->query('amenities', []);

        if (!is_array($amenitiesInput)) {
            $legacyAmenity = trim((string) $request->query('amenity', ''));
            $amenitiesInput = $legacyAmenity !== '' ? [$legacyAmenity] : [];
        }

        return collect($amenitiesInput)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '' && array_key_exists($value, $amenityOptions))
            ->unique()
            ->values()
            ->all();
    }

    private function normalizePriceRange($minInput, $maxInput, int $boundsMin, int $boundsMax): array
    {
        $minPrice = is_numeric($minInput) ? max($boundsMin, min($boundsMax, (float) $minInput)) : null;
        $maxPrice = is_numeric($maxInput) ? max($boundsMin, min($boundsMax, (float) $maxInput)) : null;

        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        // Treat the full slider span as no explicit filter.
        if ($minPrice !== null && $maxPrice !== null && (int) round($minPrice) <= $boundsMin && (int) round($maxPrice) >= $boundsMax) {
            return [null, null];
        }

        return [$minPrice, $maxPrice];
    }
}
