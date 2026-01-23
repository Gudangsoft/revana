<?php

namespace App\Imports;

use App\Models\Marketing;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MarketingsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Check if marketing with this email already exists
        $existing = null;
        if (!empty($row['email'])) {
            $existing = Marketing::where('email', $row['email'])->first();
        }

        if ($existing) {
            // Update existing
            $existing->update([
                'name' => $row['nama'],
                'phone' => $row['telepon'] ?? null,
                'is_active' => isset($row['status']) && strtolower($row['status']) === 'aktif',
            ]);
            
            // Only update password if provided
            if (!empty($row['password'])) {
                $existing->password = Hash::make($row['password']);
                $existing->save();
            }
            
            return null; // Don't create new record
        }

        // Create new marketing
        return new Marketing([
            'name' => $row['nama'],
            'email' => $row['email'] ?? null,
            'phone' => $row['telepon'] ?? null,
            'password' => !empty($row['password']) ? Hash::make($row['password']) : null,
            'is_active' => isset($row['status']) && strtolower($row['status']) === 'aktif',
            'total_points' => 0,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telepon' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'status' => 'nullable|string',
        ];
    }
}
