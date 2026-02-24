<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Simulate landlord login
$user = User::where('role', 'landlord')->first();
if (!$user) {
    echo "No landlord user found\n";
    exit(1);
}

Auth::login($user);

try {
    // Test the landlord dashboard route
    $response = app()->call('GET', '/landlord/dashboard');
    echo "Dashboard loaded successfully\n";
    echo "Status: " . $response->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "Error loading dashboard: " . $e->getMessage() . "\n";
}