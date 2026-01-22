<?php

namespace App\Imports;

use App\Models\Pic;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Str;

class PicsImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;
    
    protected $rowCount = 0;
    protected $updatedCount = 0;
    protected $createdCount = 0;

    public function model(array $row)
    {
        $this->rowCount++;

        // Normalize column names (support various formats)
        $name = $row['nama'] ?? $row['name'] ?? $row['Nama'] ?? $row['Name'] ?? null;
        $username = $row['username'] ?? $row['Username'] ?? $row['user'] ?? $row['User'] ?? null;
        $email = $row['email'] ?? $row['Email'] ?? null;
        $phone = $row['telepon'] ?? $row['phone'] ?? $row['Telepon'] ?? $row['Phone'] ?? $row['no_hp'] ?? $row['No HP'] ?? null;
        $status = $row['status'] ?? $row['Status'] ?? $row['is_active'] ?? null;

        // Clean up whitespace
        $email = !empty($email) ? trim($email) : null;
        
        // Validate email format if provided
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Skip rows with invalid email
            return null;
        }

        if (empty($name)) {
            return null;
        }

        // Determine is_active status
        $isActive = true;
        if ($status !== null) {
            if (is_string($status)) {
                $statusLower = strtolower(trim($status));
                $isActive = in_array($statusLower, ['aktif', 'active', 'yes', 'ya', '1', 'true']);
            } else {
                $isActive = (bool) $status;
            }
        }

        // Check if PIC exists by username, email or name
        $existingPic = null;
        if (!empty($username)) {
            $existingPic = Pic::where('username', $username)->first();
        }
        if (!$existingPic && !empty($email)) {
            $existingPic = Pic::where('email', $email)->first();
        }
        if (!$existingPic && !empty($name)) {
            $existingPic = Pic::where('name', $name)->first();
        }

        if ($existingPic) {
            // Update existing
            $existingPic->update([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'is_active' => $isActive,
                'password' => bcrypt('pic@apjikom.or.id'), // Set default password
            ]);
            $this->updatedCount++;
            return null;
        }

        // Create new
        $this->createdCount++;
        return new Pic([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'is_active' => $isActive,
            'password' => bcrypt('pic@apjikom.or.id'), // Set default password
        ]);
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }
    
    public function getSkippedCount(): int
    {
        return count($this->failures());
    }
}
