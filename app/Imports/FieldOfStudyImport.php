<?php

namespace App\Imports;

use App\Models\FieldOfStudy;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class FieldOfStudyImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip if name is empty
        if (empty($row['name'] ?? $row['nama'])) {
            return null;
        }

        $name = $row['name'] ?? $row['nama'];
        $description = $row['description'] ?? $row['deskripsi'] ?? null;
        $order = $row['order'] ?? $row['urutan'] ?? 0;
        $isActive = isset($row['is_active']) ? ($row['is_active'] == 1 || strtolower($row['is_active']) == 'aktif' || strtolower($row['is_active']) == 'yes') : 
                    (isset($row['status']) ? ($row['status'] == 1 || strtolower($row['status']) == 'aktif' || strtolower($row['status']) == 'yes') : true);

        // Check if field already exists
        $existing = FieldOfStudy::where('name', $name)->first();
        
        if ($existing) {
            // Update existing
            $existing->update([
                'description' => $description,
                'order' => $order,
                'is_active' => $isActive,
            ]);
            return null;
        }

        // Create new
        return new FieldOfStudy([
            'name' => $name,
            'description' => $description,
            'order' => $order,
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
}
