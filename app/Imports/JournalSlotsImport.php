<?php

namespace App\Imports;

use App\Models\JournalSlot;
use App\Models\JournalMaster;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class JournalSlotsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    protected $imported = 0;
    protected $updated = 0;
    protected $userId;
    protected $journalCache = [];
    protected $skipped = [];
    protected $errors = [];

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
        $namaJurnal = $row['nama_jurnal'] ?? $row['jurnal'] ?? $row['journal'] ?? $row['journal_name'] ?? null;
        $kodeJurnal = $row['kode_jurnal'] ?? $row['journal_code'] ?? null;
        $volume = $row['volume'] ?? $row['vol'] ?? null;
        $nomor = $row['nomor'] ?? $row['no'] ?? $row['number'] ?? null;
        $bulan = $row['bulan'] ?? $row['month'] ?? null;
        $tahun = $row['tahun'] ?? $row['year'] ?? date('Y');
        $jumlahSlot = $row['jumlah_slot'] ?? $row['slots'] ?? $row['slot'] ?? 1;
        $kodeSlot = $row['kode_slot'] ?? $row['slot_code'] ?? null;

        // Handle is_active
        $isActive = true;
        if (isset($row['is_active'])) {
            $val = $row['is_active'];
            $isActive = ($val == 1 || strtolower($val) == 'aktif' || strtolower($val) == 'yes' || strtolower($val) == 'true' || strtolower($val) == 'ya');
        } elseif (isset($row['status'])) {
            $val = $row['status'];
            $isActive = ($val == 1 || strtolower($val) == 'aktif' || strtolower($val) == 'yes' || strtolower($val) == 'true' || strtolower($val) == 'ya');
        }

        // Skip if required fields are empty
        if (empty($namaJurnal) && empty($kodeJurnal)) {
            $this->skipped[] = "Baris kosong atau tidak ada nama jurnal";
            return null;
        }

        if (empty($volume) || empty($nomor) || empty($bulan)) {
            $this->skipped[] = "Data tidak lengkap: volume={$volume}, nomor={$nomor}, bulan={$bulan}";
            return null;
        }

        // Find journal by kode_jurnal or nama_jurnal
        $journalMaster = $this->findJournal($kodeJurnal, $namaJurnal);
        
        if (!$journalMaster) {
            $this->errors[] = "Jurnal tidak ditemukan: {$namaJurnal} (kode: {$kodeJurnal})";
            return null; // Skip if journal not found
        }

        // Check if slot already exists
        $existing = null;
        if (!empty($kodeSlot)) {
            $existing = JournalSlot::where('kode_slot', $kodeSlot)->first();
        }
        if (!$existing) {
            $existing = JournalSlot::where('journal_master_id', $journalMaster->id)
                ->where('volume', $volume)
                ->where('nomor', $nomor)
                ->where('tahun', $tahun)
                ->first();
        }

        if ($existing) {
            // Update existing
            $existing->update([
                'bulan' => $bulan,
                'jumlah_slot' => max((int) $jumlahSlot, $existing->slot_terpakai),
                'is_active' => $isActive,
            ]);
            $this->updated++;
            return null;
        }

        // Create new
        $this->imported++;
        return new JournalSlot([
            'kode_slot' => $kodeSlot, // Will auto-generate if null
            'journal_master_id' => $journalMaster->id,
            'volume' => $volume,
            'nomor' => $nomor,
            'bulan' => $bulan,
            'tahun' => (int) $tahun,
            'jumlah_slot' => (int) $jumlahSlot,
            'slot_terpakai' => 0,
            'is_active' => $isActive,
            'created_by' => $this->userId,
        ]);
    }

    /**
     * Find journal by code or name
     */
    protected function findJournal($kodeJurnal, $namaJurnal)
    {
        $cacheKey = $kodeJurnal ?: $namaJurnal;
        
        if (isset($this->journalCache[$cacheKey])) {
            return $this->journalCache[$cacheKey];
        }

        $journal = null;
        
        // 1. Try exact kode_jurnal match
        if (!empty($kodeJurnal)) {
            $journal = JournalMaster::where('kode_jurnal', $kodeJurnal)->first();
        }
        
        // 2. Try exact nama_jurnal match
        if (!$journal && !empty($namaJurnal)) {
            $journal = JournalMaster::where('nama_jurnal', $namaJurnal)->first();
        }
        
        // 3. Try LIKE match (case insensitive)
        if (!$journal && !empty($namaJurnal)) {
            // Remove special characters for better matching
            $searchName = trim($namaJurnal);
            $journal = JournalMaster::whereRaw('LOWER(nama_jurnal) LIKE ?', ['%' . strtolower($searchName) . '%'])->first();
        }
        
        // 4. Try partial word match (split by spaces and search for main words)
        if (!$journal && !empty($namaJurnal)) {
            $words = explode(' ', $namaJurnal);
            $mainWords = array_filter($words, function($word) {
                return strlen($word) > 3 && !in_array(strtolower($word), ['jurnal', 'journal', 'the', 'dan', 'and']);
            });
            
            if (count($mainWords) > 0) {
                $query = JournalMaster::query();
                foreach ($mainWords as $word) {
                    $query->whereRaw('LOWER(nama_jurnal) LIKE ?', ['%' . strtolower($word) . '%']);
                }
                $journal = $query->first();
            }
        }

        if ($journal) {
            $this->journalCache[$cacheKey] = $journal;
        }

        return $journal;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [];
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getUpdatedCount(): int
    {
        return $this->updated;
    }

    public function getSkipped(): array
    {
        return $this->skipped;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
