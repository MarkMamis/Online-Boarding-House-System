<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;
use App\Services\GeocodingService;

class GeocodeProperties extends Command
{
    protected $signature = 'properties:geocode {--force : Re-geocode even if coordinates exist}';
    protected $description = 'Populate latitude/longitude for properties via GeocodingService';

    public function handle(GeocodingService $geo)
    {
        $force = $this->option('force');
        $count = 0;
        $this->info('Starting geocoding...');
        Property::chunk(50, function($chunk) use ($geo, $force, &$count){
            foreach ($chunk as $property) {
                if (!$force && $property->latitude && $property->longitude) {
                    continue;
                }
                $result = $geo->geocodeAddress($property->address);
                if ($result) {
                    $property->latitude = $result['lat'];
                    $property->longitude = $result['lng'];
                    $property->save();
                    $this->line("✔ {$property->name} geocoded ({$property->latitude}, {$property->longitude})");
                    $count++;
                } else {
                    $this->warn("✖ {$property->name} failed to geocode");
                }
            }
        });
        $this->info("Done. Geocoded {$count} properties.");
        return Command::SUCCESS;
    }
}
