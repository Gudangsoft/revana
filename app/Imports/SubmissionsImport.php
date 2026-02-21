<?php

namespace App\Imports;

use App\Models\Submission;
use App\Models\JournalSlot;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SubmissionsImport implements ToModel, WithHeadingRow, WithValidation
{

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
        $idArtikel = $row['id_artikel'] ?? $row['id artikel'] ?? $row['article_id'] ?? null;
        $judulArtikel = $row['judul_artikel'] ?? $row['judul artikel'] ?? $row['judul'] ?? $row['title'] ?? null;
        $linkArtikel = $row['link_artikel'] ?? $row['link artikel'] ?? $row['link'] ?? null;
        $namaPenulis = $row['nama_penulis'] ?? $row['nama penulis'] ?? $row['penulis'] ?? $row['author'] ?? null;
        $noHpPenulis = $row['no_hp_penulis'] ?? $row['no hp penulis'] ?? $row['no_hp'] ?? $row['hp'] ?? $row['phone'] ?? null;
        $usernameAuthor = $row['username_author'] ?? $row['username author'] ?? $row['username'] ?? null;
        $passwordAuthor = $row['password_author'] ?? $row['password author'] ?? $row['password'] ?? null;
        $picMarketing = $row['pic_marketing'] ?? $row['pic marketing'] ?? $row['pic'] ?? null;
        $tanggalSubmit = $row['tanggal_submit'] ?? $row['tanggal submit'] ?? $row['tanggal'] ?? $row['date'] ?? null;
        
        // Journal slot - try to find by kode_slot or combination
        $kodeSlot = $row['kode_slot'] ?? $row['kode slot'] ?? $row['slot'] ?? null;
        $journalSlotId = $row['journal_slot_id'] ?? null;

        // Skip if required fields are empty
        if (empty($idArtikel) || empty($judulArtikel) || empty($namaPenulis)) {
            return null;
        }

        // Find journal slot
        if (!$journalSlotId && $kodeSlot) {
            $slot = JournalSlot::where('kode_slot', $kodeSlot)->first();
            $journalSlotId = $slot?->id;
        }

        // If still no slot, get the first available one
        if (!$journalSlotId) {
            $slot = JournalSlot::where('is_active', true)
                ->whereRaw('slot_terpakai < jumlah_slot')
                ->first();
            $journalSlotId = $slot?->id;
        }

        // Parse tanggal submit
        $parsedDate = null;
        if ($tanggalSubmit) {
            try {
                if (is_numeric($tanggalSubmit)) {
                    // Excel serial date
                    $parsedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalSubmit);
                } else {
                    $parsedDate = \Carbon\Carbon::parse($tanggalSubmit);
                }
            } catch (\Exception $e) {
                $parsedDate = now();
            }
        } else {
            $parsedDate = now();
        }

        // Check if submission already exists by id_artikel
        $existing = Submission::where('id_artikel', $idArtikel)->first();

        if ($existing) {
            // Update existing
            $existing->update([
                'judul_artikel' => $judulArtikel ?? $existing->judul_artikel,
                'link_artikel' => $linkArtikel ?? $existing->link_artikel,
                'nama_penulis' => $namaPenulis ?? $existing->nama_penulis,
                'no_hp_penulis' => $noHpPenulis ?? $existing->no_hp_penulis,
                'username_author' => $usernameAuthor ?? $existing->username_author,
                'password_author' => $passwordAuthor ?? $existing->password_author,
                'pic_marketing' => $picMarketing ?? $existing->pic_marketing,
            ]);
            $this->updated++;
            return null;
        }

        // Create new
        $this->imported++;
        return new Submission([
            'journal_slot_id' => $journalSlotId,
            'id_artikel' => $idArtikel,
            'judul_artikel' => $judulArtikel,
            'link_artikel' => $linkArtikel,
            'nama_penulis' => $namaPenulis,
            'no_hp_penulis' => $noHpPenulis,
            'username_author' => $usernameAuthor,
            'password_author' => $passwordAuthor,
            'pic_marketing' => $picMarketing,
            'petugas_submit_id' => $this->userId,
            'tanggal_submit' => $parsedDate,
            'status' => 'SUBMITTED',
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
     * Get imported count
     */
    public function getImportedCount(): int
    {
        return $this->imported;
    }

    /**
     * Get updated count
     */
    public function getUpdatedCount(): int
    {
        return $this->updated;
    }
}
