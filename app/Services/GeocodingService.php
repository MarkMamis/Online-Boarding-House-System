<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Geocoder; // from toin0u/geocoder-laravel

class GeocodingService
{
    public function geocodeAddress(?string $address): ?array
    {
        if (!$address || trim($address) === '') {
            return null;
        }
        try {
            $collection = Geocoder::geocode($address)->get();
            if ($collection->isEmpty()) {
                return null;
            }
            $first = $collection->first();
            $lat = $first->getCoordinates()->getLatitude();
            $lng = $first->getCoordinates()->getLongitude();
            if ($lat === null || $lng === null) {
                return null;
            }
            return [ 'lat' => $lat, 'lng' => $lng ];
        } catch (\Throwable $e) {
            Log::warning('Geocode failed', ['address' => $address, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
