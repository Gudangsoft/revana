<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningForm extends Model
{
    protected $fillable = [
        'submission_id', 'screened_by', 'checklist',
        'similarity_score', 'keputusan', 'catatan',
        'recipient_email', 'email_sent_at',
    ];

    protected $casts = [
        'checklist'    => 'array',
        'email_sent_at' => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function screener()
    {
        return $this->belongsTo(Pic::class, 'screened_by');
    }

    /** Hitung total item yang dicentang (true) dari checklist */
    public function countPassed(): int
    {
        if (!$this->checklist) return 0;
        return count(array_filter($this->checklist, fn($v) => $v === true));
    }

    /** Total item yang dinilai (true atau false, bukan null) */
    public function countAssessed(): int
    {
        if (!$this->checklist) return 0;
        return count(array_filter($this->checklist, fn($v) => $v !== null));
    }

    public static function keputusanLabel(string $k): string
    {
        return match($k) {
            'diterima' => 'Diterima (Proceed to Review)',
            'revisi'   => 'Perlu Revisi Awal',
            'ditolak'  => 'Ditolak (Desk Reject)',
            default    => $k,
        };
    }

    public static function keputusanColor(string $k): string
    {
        return match($k) {
            'diterima' => 'success',
            'revisi'   => 'warning',
            'ditolak'  => 'danger',
            default    => 'secondary',
        };
    }
}