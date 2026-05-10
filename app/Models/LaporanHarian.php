<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanHarian extends Model
{
    protected $table = 'laporan_harian';

    protected $fillable = [
        'pic_id',
        'tanggal',
        'target_kerja',
        'laporan_kinerja',
        'bukti_hasil',
        'capaian_hasil',
    ];

    protected $casts = [
        'tanggal'       => 'date',
        'capaian_hasil' => 'integer',
    ];

    public function pic()
    {
        return $this->belongsTo(Pic::class);
    }
}
