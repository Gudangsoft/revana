<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pic;
use Illuminate\Support\Facades\Hash;

// Show existing PICs
echo "=== Existing PICs ===\n";
$existingPics = Pic::all();
foreach ($existingPics as $pic) {
    echo "ID: {$pic->id} - {$pic->name} ({$pic->email})\n";
}
echo "Total: " . $existingPics->count() . " PICs\n\n";

// Add dummy PICs if less than 5
if ($existingPics->count() < 5) {
    $dummyPics = [
        [
            'name' => 'Siti Rahayu',
            'email' => 'siti.rahayu@apji.org',
            'phone' => '081234567801',
            'position' => 'Editor Senior',
        ],
        [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@apji.org',
            'phone' => '081234567802',
            'position' => 'Editor',
        ],
        [
            'name' => 'Dewi Lestari',
            'email' => 'dewi.lestari@apji.org',
            'phone' => '081234567803',
            'position' => 'Author Coordinator',
        ],
        [
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad.fauzi@apji.org',
            'phone' => '081234567804',
            'position' => 'Production Editor',
        ],
        [
            'name' => 'Rina Wati',
            'email' => 'rina.wati@apji.org',
            'phone' => '081234567805',
            'position' => 'Layout Editor',
        ],
    ];

    echo "=== Creating Dummy PICs ===\n";
    foreach ($dummyPics as $picData) {
        // Check if email exists
        if (Pic::where('email', $picData['email'])->exists()) {
            echo "Skipped (exists): {$picData['email']}\n";
            continue;
        }
        
        $pic = Pic::create([
            'name' => $picData['name'],
            'email' => $picData['email'],
            'password' => Hash::make('password123'),
            'phone' => $picData['phone'],
            'position' => $picData['position'],
            'is_active' => true,
        ]);
        
        echo "Created: {$pic->name} ({$pic->email}) - ID: {$pic->id}\n";
    }
}

echo "\n=== Final PIC List ===\n";
$allPics = Pic::all();
foreach ($allPics as $pic) {
    $status = $pic->is_active ? 'Active' : 'Inactive';
    echo "ID: {$pic->id} - {$pic->name} ({$pic->email}) - {$pic->position} [{$status}]\n";
}
echo "Total: " . $allPics->count() . " PICs\n";
