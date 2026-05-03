<?php
/**
 * Fix notification URLs to use production domain
 * Run: php fix_notification_urls.php
 */

require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasTable('notifications')) {
    echo "Notifications table does not exist. Skipping.\n";
    exit(0);
}

$productionUrl = 'https://mcc-boardinghouse.online';
$localhostPatterns = [
    'http://127.0.0.1:8000',
    'http://localhost:8000',
    'http://192.168',
];

$fixed = 0;
$notifications = DB::table('notifications')->get();

foreach ($notifications as $notification) {
    $data = json_decode($notification->data, true);
    
    if (!is_array($data) || empty($data['url'])) {
        continue;
    }
    
    $url = $data['url'];
    $originalUrl = $url;
    
    // Replace localhost patterns
    foreach ($localhostPatterns as $pattern) {
        if (strpos($url, $pattern) === 0) {
            $url = str_replace($pattern, $productionUrl, $url);
            break;
        }
    }
    
    // Only update if URL changed
    if ($url !== $originalUrl) {
        $data['url'] = $url;
        DB::table('notifications')
            ->where('id', $notification->id)
            ->update(['data' => json_encode($data)]);
        $fixed++;
        echo "Fixed: $originalUrl → $url\n";
    }
}

echo "\nTotal notifications fixed: $fixed\n";
echo "✓ All notification URLs now use production domain: $productionUrl\n";
