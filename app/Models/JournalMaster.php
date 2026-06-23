<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_jurnal',
        'nama_jurnal',
        'publisher',
        'link_jurnal',
        'accreditation',
        'kategori',
        'jenis_jurnal',
        'points',
        'is_active',
        'created_by',
        // LOA fields
        'kode_singkat',
        'e_issn',
        'logo_path',
        'header_image_path',
        'footer_image_path',
        'p_issn',
        'accreditation_logo_path',
        'link_sk_akreditasi',
        'editor_name',
        'editor_title',
        'editor_signature_path',
        'primary_color',
        'secondary_color',
        'loa_kota',
        'loa_tanggal',
        'loa_auto_send',
        'loa_auto_trigger',
        'loa_language',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journal) {
            $journal->points = self::calculatePoints($journal->accreditation);
            
            // Auto generate kode_jurnal if not set
            if (empty($journal->kode_jurnal)) {
                $journal->kode_jurnal = self::generateKodeJurnal();
            }
        });

        static::updating(function ($journal) {
            if ($journal->isDirty('accreditation')) {
                $journal->points = self::calculatePoints($journal->accreditation);
            }
        });
    }

    public static function generateKodeJurnal()
    {
        $prefix = 'JRN';
        $year = date('Y');
        $lastJournal = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastJournal ? (int) substr($lastJournal->kode_jurnal, -4) + 1 : 1;
        
        return $prefix . $year . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public static function calculatePoints($accreditation)
    {
        $accreditationModel = Accreditation::where('name', $accreditation)->first();
        
        if ($accreditationModel) {
            return $accreditationModel->points;
        }

        return match($accreditation) {
            'SINTA 1' => 100,
            'SINTA 2' => 80,
            'SINTA 3' => 60,
            'SINTA 4' => 40,
            'SINTA 5' => 20,
            'SINTA 6' => 10,
            default => 0,
        };
    }

    public function slots()
    {
        return $this->hasMany(JournalSlot::class, 'journal_master_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accreditationModel()
    {
        return $this->belongsTo(Accreditation::class, 'accreditation', 'name');
    }

    // Calculate total slots across all slot records
    public function getTotalSlotsAttribute()
    {
        return $this->slots()->sum('jumlah_slot');
    }

    // Calculate used slots
    public function getUsedSlotsAttribute()
    {
        return $this->slots()->sum('slot_terpakai');
    }

    // Calculate available slots
    public function getAvailableSlotsAttribute()
    {
        return $this->total_slots - $this->used_slots;
    }
}
