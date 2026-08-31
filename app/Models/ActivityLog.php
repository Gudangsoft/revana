<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'causer_name',
        'causer_guard',
        'event',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Fields to track on Submission updates
    public const SUBMISSION_TRACKED_FIELDS = [
        'judul_artikel'     => 'Judul Artikel',
        'nama_penulis'      => 'Nama Penulis',
        'id_artikel'        => 'ID Artikel',
        'link_artikel'      => 'Link Submit',
        'no_hp_penulis'     => 'No HP Penulis',
        'status'            => 'Status',
        'marketing_id'      => 'Marketing',
        'petugas_submit_id' => 'PIC Submit',
        'journal_slot_id'   => 'Slot Jurnal',
        'program_type'      => 'Program',
        'process_type'      => 'Tipe Proses',
        'notes'             => 'Catatan',
    ];

    /**
     * Record an activity log entry.
     * Resolves the current authenticated user from any guard.
     */
    public static function record(string $event, Model $subject, array $old = [], array $new = []): void
    {
        $causerName  = 'System';
        $causerGuard = 'system';

        foreach (['web', 'pic', 'marketing'] as $guard) {
            if (Auth::guard($guard)->check()) {
                $causerName  = Auth::guard($guard)->user()->name;
                $causerGuard = $guard;
                break;
            }
        }

        static::create([
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->getKey(),
            'causer_name'  => $causerName,
            'causer_guard' => $causerGuard,
            'event'        => $event,
            'old_values'   => $old ?: null,
            'new_values'   => $new ?: null,
            'ip_address'   => request()->ip(),
        ]);
    }

    /**
     * Diff two arrays and return only changed keys.
     */
    public static function diff(array $before, array $after): array
    {
        $changed = [];
        foreach ($before as $key => $oldVal) {
            $newVal = $after[$key] ?? null;
            if ((string)$oldVal !== (string)$newVal) {
                $changed[$key] = $oldVal;
            }
        }
        return $changed;
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created'        => 'Dibuat',
            'updated'        => 'Diubah',
            'deleted'        => 'Dihapus',
            'status_changed' => 'Status Diubah',
            default          => ucfirst($this->event),
        };
    }

    public function getEventBadgeClassAttribute(): string
    {
        return match ($this->event) {
            'created'        => 'bg-success',
            'updated'        => 'bg-primary',
            'deleted'        => 'bg-danger',
            'status_changed' => 'bg-warning text-dark',
            default          => 'bg-secondary',
        };
    }
}
