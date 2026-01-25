<?php

namespace App\Imports;

use App\Models\JournalMaster;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class JournalMastersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    protected $imported = 0;
    protected $updated = 0;
    protected $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId ?? auth()->id();
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Support both English and Indonesian column headers
        $namaJurnal = $row['nama_jurnal'] ?? $row['nama'] ?? $row['name'] ?? $row['journal_name'] ?? null;
        $publisher = $row['publisher'] ?? $row['penerbit'] ?? null;
        $linkJurnal = $row['link_jurnal'] ?? $row['link'] ?? $row['url'] ?? $row['website'] ?? null;
        $accreditation = $row['accreditation'] ?? $row['akreditasi'] ?? null;
        $kodeJurnal = $row['kode_jurnal'] ?? $row['kode'] ?? $row['code'] ?? null;
        $kategori = $row['kategori'] ?? $row['category'] ?? null;
        $jenisJurnal = $row['jenis_jurnal'] ?? $row['jenis'] ?? $row['type'] ?? null;

        // Handle is_active with multiple possible column names and values
        $isActive = true; // default
        if (isset($row['is_active'])) {
            $val = $row['is_active'];
            $isActive = ($val == 1 || strtolower($val) == 'aktif' || strtolower($val) == 'yes' || strtolower($val) == 'true' || strtolower($val) == 'ya');
        } elseif (isset($row['status'])) {
            $val = $row['status'];
            $isActive = ($val == 1 || strtolower($val) == 'aktif' || strtolower($val) == 'yes' || strtolower($val) == 'true' || strtolower($val) == 'ya');
        }

        // Skip if nama_jurnal is empty
        if (empty($namaJurnal)) {
            return null;
        }

        // Check if journal already exists by kode_jurnal or nama_jurnal
        $existing = null;
        if (!empty($kodeJurnal)) {
            $existing = JournalMaster::where('kode_jurnal', $kodeJurnal)->first();
        }
        if (!$existing && !empty($namaJurnal)) {
            $existing = JournalMaster::where('nama_jurnal', $namaJurnal)->first();
        }

        if ($existing) {
            // Update existing
            $existing->update([
                'publisher' => $publisher ?? $existing->publisher,
                'link_jurnal' => $linkJurnal ?? $existing->link_jurnal,
                'accreditation' => $accreditation ?? $existing->accreditation,
                'kategori' => $kategori ?? $existing->kategori,
                'jenis_jurnal' => $jenisJurnal ?? $existing->jenis_jurnal,
                'is_active' => $isActive,
            ]);
            $this->updated++;
            return null;
        }

        // Create new
        $this->imported++;
        return new JournalMaster([
            'kode_jurnal' => $kodeJurnal, // Will auto-generate if null
            'nama_jurnal' => $namaJurnal,
            'publisher' => $publisher,
            'link_jurnal' => $linkJurnal,
            'accreditation' => $accreditation,
            'kategori' => $kategori,
            'jenis_jurnal' => $jenisJurnal,
            'is_active' => $isActive,
            'created_by' => $this->userId,
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
