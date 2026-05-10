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
        'validated_at',
        'validated_by',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'capaian_hasil'=> 'integer',
        'validated_at' => 'datetime',
    ];

    public function getIsValidatedAttribute(): bool
    {
        return !is_null($this->validated_at);
    }

    public function validator()
    {
        return $this->belongsTo(\App\Models\User::class, 'validated_by');
    }

    public function pic()
    {
        return $this->belongsTo(Pic::class);
    }
}
