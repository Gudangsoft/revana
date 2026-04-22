<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("ALTER TABLE submission_histories MODIFY COLUMN step ENUM('submit', 'editor1', 'editor2', 'editor3', 'author1', 'author2', 'reviewer1', 'reviewer2', 'production', 'validator', 'fasttrack') DEFAULT NULL");
    echo "Successfully updated ENUM column to include validator and fasttrack!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
