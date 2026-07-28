<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Settings Table Check ===\n";

// Check if table exists
if (Schema::hasTable('settings')) {
    echo "✓ Table 'settings' exists\n";
    
    // Check columns
    $columns = Schema::getColumnListing('settings');
    echo "Columns: " . implode(', ', $columns) . "\n";
    
    // Check if key and value columns exist
    if (in_array('key', $columns) && in_array('value', $columns)) {
        echo "✓ 'key' and 'value' columns exist\n";
    } else {
        echo "✗ Missing 'key' or 'value' columns!\n";
    }
    
    // Count records
    $count = DB::table('settings')->count();
    echo "Total records: $count\n\n";
    
    // Show all records
    $records = DB::table('settings')->get();
    foreach ($records as $r) {
        echo "id={$r->id} | key=" . ($r->key ?? 'NULL') . " | value=" . substr($r->value ?? 'NULL', 0, 80) . "\n";
    }
    
    // Test Setting model
    echo "\n=== Test Setting Model ===\n";
    $token = App\Models\Setting::get('fonnte_api_token');
    echo "fonnte_api_token: " . ($token ?? 'NULL/EMPTY') . "\n";
    
    // Try writing
    echo "\n=== Test Writing ===\n";
    try {
        App\Models\Setting::set('test_key_123', 'test_value_123');
        echo "✓ Write succeeded\n";
        
        $readback = App\Models\Setting::get('test_key_123');
        echo "Read back: " . ($readback ?? 'NULL') . "\n";
        
        // Cleanup
        DB::table('settings')->where('key', 'test_key_123')->delete();
        echo "✓ Cleanup done\n";
    } catch (Exception $e) {
        echo "✗ Write failed: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "✗ Table 'settings' does NOT exist!\n";
}
