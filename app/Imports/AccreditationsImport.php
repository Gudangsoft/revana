<?php

namespace App\Imports;

use App\Models\Accreditation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class AccreditationsImport implements ToModel, WithHeadingRow, WithValidation
{

    protected $imported = 0;
    protected $updated = 0;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Support both English and Indonesian column headers
        $name = $row['name'] ?? $row['nama'] ?? $row['nama_akreditasi'] ?? null;
        $points = $row['points'] ?? $row['poin'] ?? $row['point'] ?? 0;
        $description = $row['description'] ?? $row['deskripsi'] ?? null;
        
        // Handle is_active with multiple possible column names and values
        $isActive = true; // default
        if (isset($row['is_active'])) {
            $val = $row['is_active'];
            $isActive = ($val == 1 || strtolower($val) == 'aktif' || strtolower($val) == 'yes' || strtolower($val) == 'true' || strtolower($val) == 'ya');
        } elseif (isset($row['status'])) {
            $val = $row['status'];
            $isActive = ($val == 1 || strtolower($val) == 'aktif' || strtolower($val) == 'yes' || strtolower($val) == 'true' || strtolower($val) == 'ya');
        }

        // Skip if name is empty
        if (empty($name)) {
            return null;
        }

        // Check if accreditation already exists
        $existing = Accreditation::where('name', $name)->first();

        if ($existing) {
            // Update existing
            $existing->update([
                'points' => (int) $points,
                'description' => $description,
                'is_active' => $isActive,
            ]);
            $this->updated++;
            return null;
        }

        // Create new
        $this->imported++;
        return new Accreditation([
            'name' => $name,
            'points' => (int) $points,
            'description' => $description,
            'is_active' => $isActive,
        ]);
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            // No strict validation - will handle empty rows in model() method
        ];
    }

    /**
     * Get import statistics
     */
    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getUpdatedCount(): int
    {
        return $this->updated;
    }
}
