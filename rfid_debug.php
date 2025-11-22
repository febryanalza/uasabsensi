<?php

// Simple test script untuk debug RFID controller
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AvailableRfid;
use App\Http\Controllers\Web\RfidController;
use Illuminate\Http\Request;

echo "=== RFID Debug Test ===\n";

echo "1. Testing database connection...\n";
try {
    $count = AvailableRfid::count();
    echo "✓ Database OK - Total RFID cards: $count\n";
} catch (Exception $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Testing AvailableRfid model...\n";
try {
    $firstCard = AvailableRfid::first();
    if ($firstCard) {
        echo "✓ First card found: {$firstCard->card_number} (Status: {$firstCard->status})\n";
    } else {
        echo "✗ No cards found in database\n";
    }
} catch (Exception $e) {
    echo "✗ Model Error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing RfidController getStatistics...\n";
try {
    $controller = new RfidController();
    $response = $controller->getStatistics();
    $data = json_decode($response->getContent(), true);
    
    echo "✓ Statistics response:\n";
    echo "  - Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    if ($data['success']) {
        echo "  - Total cards: " . $data['statistics']['total_cards'] . "\n";
        echo "  - Available: " . $data['statistics']['available_cards'] . "\n";
        echo "  - Assigned: " . $data['statistics']['assigned_cards'] . "\n";
    }
} catch (Exception $e) {
    echo "✗ Controller Error: " . $e->getMessage() . "\n";
}

echo "\n4. Testing RfidController getData...\n";
try {
    $controller = new RfidController();
    $request = new Request();
    $response = $controller->getData($request);
    $data = json_decode($response->getContent(), true);
    
    echo "✓ Data response:\n";
    echo "  - Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    if ($data['success']) {
        echo "  - Items count: " . count($data['data']) . "\n";
        echo "  - Total: " . $data['pagination']['total'] . "\n";
        if (!empty($data['data'])) {
            $first = $data['data'][0];
            echo "  - First item: {$first['card_number']} ({$first['status']})\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Controller Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";