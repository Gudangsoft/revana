<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanHarianLog extends Model
{
    public $timestamps = false;

    protected $table = 'laporan_harian_logs';

    protected $fillable = [
        'laporan_harian_id',
        'actor_type',
        'actor_id',
        'actor_name',
        'action',
        'changes',
    ];

    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanHarian::class, 'laporan_harian_id');
    }

    public static function record(LaporanHarian $laporan, string $actorType, int $actorId, string $actorName, string $action, array $changes = []): void
    {
        static::create([
            'laporan_harian_id' => $laporan->id,
            'actor_type'        => $actorType,
            'actor_id'          => $actorId,
            'actor_name'        => $actorName,
            'action'            => $action,
            'changes'           => empty($changes) ? null : $changes,
        ]);
    }

    public function actionLabel(): string
    {
        return match($this->action) {
            'created'     => 'Membuat catatan',
            'updated'     => 'Mengubah catatan',
            'validated'   => 'Memvalidasi',
            'unvalidated' => 'Membatalkan validasi',
            'catatan'     => 'Menambah catatan admin',
            default       => $this->action,
        };
    }

    public function actionColor(): string
    {
        return match($this->action) {
            'created'     => 'success',
            'updated'     => 'primary',
            'validated'   => 'success',
            'unvalidated' => 'danger',
            'catatan'     => 'info',
            default       => 'secondary',
        };
    }

    public static function fieldLabel(string $field): string
    {
        return match($field) {
            'judul_kegiatan'  => 'Judul Kegiatan',
            'target_kerja'    => 'Catatan Kerja',
            'laporan_kinerja' => 'Laporan Kinerja',
            'bukti_hasil'     => 'Bukti Hasil',
            'capaian_hasil'   => 'Capaian Hasil',
            'catatan_admin'   => 'Catatan Admin',
            default           => $field,
        };
    }
}
