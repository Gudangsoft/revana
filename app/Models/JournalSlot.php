<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_slot',
        'journal_master_id',
        'volume',
        'nomor',
        'bulan',
        'tahun',
        'jumlah_slot',
        'slot_terpakai',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tahun' => 'integer',
        'jumlah_slot' => 'integer',
        'slot_terpakai' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($slot) {
            // Auto generate kode_slot if not set
            if (empty($slot->kode_slot)) {
                $slot->kode_slot = self::generateKodeSlot($slot);
            }
        });
    }

    public static function generateKodeSlot($slot)
    {
        $prefix = 'SLT';
        $year = $slot->tahun ?? date('Y');
        
        // Try to get the last slot for this year
        $lastSlot = self::where('kode_slot', 'like', $prefix . $year . '%')
            ->orderBy('kode_slot', 'desc')
            ->first();
        
        if ($lastSlot) {
            // Extract number from last kode_slot
            $lastNumber = (int) substr($lastSlot->kode_slot, -4);
            $number = $lastNumber + 1;
        } else {
            $number = 1;
        }
        
        // Generate and check if exists (retry up to 100 times to avoid collision)
        $attempts = 0;
        do {
            $kodeSlot = $prefix . $year . str_pad($number, 4, '0', STR_PAD_LEFT);
            $exists = self::where('kode_slot', $kodeSlot)->exists();
            
            if ($exists) {
                $number++;
                $attempts++;
            }
        } while ($exists && $attempts < 100);
        
        return $kodeSlot;
    }

    public function journalMaster()
    {
        return $this->belongsTo(JournalMaster::class, 'journal_master_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'journal_slot_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Get available slots
    public function getSlotTersediaAttribute()
    {
        return $this->jumlah_slot - $this->slot_terpakai;
    }

    // Check if slot is full
    public function getIsFullAttribute()
    {
        return $this->slot_terpakai >= $this->jumlah_slot;
    }

    // Get display name
    public function getDisplayNameAttribute()
    {
        return $this->journalMaster->nama_jurnal . ' - Vol.' . $this->volume . ' No.' . $this->nomor . ' (' . $this->bulan . ' ' . $this->tahun . ')';
    }

    // Get bulan options
    public static function getBulanOptions()
    {
        return [
            'Januari' => 'Januari',
            'Februari' => 'Februari',
            'Maret' => 'Maret',
            'April' => 'April',
            'Mei' => 'Mei',
            'Juni' => 'Juni',
            'Juli' => 'Juli',
            'Agustus' => 'Agustus',
            'September' => 'September',
            'Oktober' => 'Oktober',
            'November' => 'November',
            'Desember' => 'Desember',
        ];
    }
}
