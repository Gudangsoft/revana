<?php

namespace App\Imports;

use App\Models\Pic;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class PicsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $rowCount = 0;
    protected $updatedCount = 0;
    protected $createdCount = 0;

    public function model(array $row)
    {
        $this->rowCount++;

        // Normalize column names (support various formats)
        $name = $row['nama'] ?? $row['name'] ?? $row['Nama'] ?? $row['Name'] ?? null;
        $email = $row['email'] ?? $row['Email'] ?? null;
        $phone = $row['telepon'] ?? $row['phone'] ?? $row['Telepon'] ?? $row['Phone'] ?? $row['no_hp'] ?? $row['No HP'] ?? null;
        $status = $row['status'] ?? $row['Status'] ?? $row['is_active'] ?? null;

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

        // Check if PIC exists by email or name
        $existingPic = null;
        if (!empty($email)) {
            $existingPic = Pic::where('email', $email)->first();
        }
        if (!$existingPic && !empty($name)) {
            $existingPic = Pic::where('name', $name)->first();
        }

        if ($existingPic) {
            // Update existing
            $existingPic->update([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'is_active' => $isActive,
            ]);
            $this->updatedCount++;
            return null;
        }

        // Create new
        $this->createdCount++;
        return new Pic([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'is_active' => $isActive,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nama' => 'nullable|string|max:255',
            '*.name' => 'nullable|string|max:255',
            '*.email' => 'nullable|email|max:255',
            '*.telepon' => 'nullable|string|max:20',
            '*.phone' => 'nullable|string|max:20',
        ];
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
}
