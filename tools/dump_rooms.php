<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rooms = App\Models\Room::with('property')
    ->orderByDesc('id')
    ->take(20)
    ->get(['id', 'property_id', 'room_number', 'status', 'price', 'capacity', 'image_path', 'created_at']);

echo $rooms->toJson(JSON_PRETTY_PRINT) . PHP_EOL;
