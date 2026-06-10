<?php

namespace App\Imports;

use App\Models\JenisJurnal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JenisJurnalImport implements ToModel, WithHeadingRow
{
    protected int $imported = 0;
    protected int $updated  = 0;

    public function model(array $row)
    {
        $name = trim($row['name'] ?? $row['nama'] ?? '');
        if (empty($name)) {
            return null;
        }

        $description = $row['description'] ?? $row['deskripsi'] ?? null;
        $rawStatus   = $row['is_active'] ?? $row['status'] ?? null;
        $isActive    = $rawStatus === null
            ? true
            : in_array(strtolower((string) $rawStatus), ['1', 'aktif', 'yes', 'true']);

        $existing = JenisJurnal::where('name', $name)->first();

        if ($existing) {
            $existing->update(['description' => $description, 'is_active' => $isActive]);
            $this->updated++;
            return null;
        }

        $this->imported++;
        return new JenisJurnal(['name' => $name, 'description' => $description, 'is_active' => $isActive]);
    }

    public function getImportedCount(): int { return $this->imported; }
    public function getUpdatedCount(): int  { return $this->updated; }
}
