<?php

// Test route helper
try {
    echo "Testing route: " . route('admin.fasttrack-management.slots.index') . "\n";
    echo "Route exists and working!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}