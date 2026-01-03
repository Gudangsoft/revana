<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AddDummySignatures extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get all reviewers without signatures
        $reviewers = User::where('role', 'reviewer')
            ->whereNull('signature')
            ->get();

        foreach ($reviewers as $reviewer) {
            // Create a simple text-based signature image
            $this->createDummySignature($reviewer);
        }

        $this->command->info('Dummy signatures added for ' . $reviewers->count() . ' reviewers');
    }

    private function createDummySignature($reviewer)
    {
        // Create a simple image with the reviewer's name
        $width = 300;
        $height = 100;
        $image = imagecreatetruecolor($width, $height);

        // Set colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $blue = imagecolorallocate($image, 0, 0, 139);

        // Fill background
        imagefill($image, 0, 0, $white);

        // Add text (signature style)
        $font = 5; // Built-in font
        $text = $reviewer->name;
        
        // Calculate position to center text
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;

        // Draw text
        imagestring($image, $font, $x, $y, $text, $blue);

        // Add underline
        imageline($image, $x, $y + $textHeight + 5, $x + $textWidth, $y + $textHeight + 5, $black);

        // Save to storage
        $filename = 'signatures/' . time() . '_' . $reviewer->id . '.png';
        $path = storage_path('app/public/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        imagepng($image, $path);
        imagedestroy($image);

        // Update user record
        $reviewer->update(['signature' => $filename]);
    }
}
