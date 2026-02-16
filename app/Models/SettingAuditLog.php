<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingAuditLog extends Model
{
    protected $fillable = [
        'action',
        'admin_name',
        'admin_guard',
        'setting_key',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
        'batch_changes',
    ];

    protected $casts = [
        'batch_changes' => 'array',
    ];

    /**
     * Log a setting change.
     */
    public static function logChange(string $action, ?string $key = null, ?string $oldValue = null, ?string $newValue = null, ?array $batch = null): self
    {
        $admin = auth()->user() ?? auth('pic')->user() ?? auth('marketing')->user();
        $guard = 'web';
        if (auth('pic')->check()) $guard = 'pic';
        if (auth('marketing')->check()) $guard = 'marketing';

        return static::create([
            'action'         => $action,
            'admin_name'     => $admin ? ($admin->name ?? $admin->username ?? 'System') : 'System',
            'admin_guard'    => $guard,
            'setting_key'    => $key,
            'old_value'      => $oldValue,
            'new_value'      => $newValue,
            'ip_address'     => request()->ip(),
            'user_agent'     => substr(request()->userAgent() ?? '', 0, 500),
            'batch_changes'  => $batch,
        ]);
    }

    /**
     * Log a batch of setting changes.
     */
    public static function logBatch(string $action, array $changes): self
    {
        return self::logChange($action, null, null, null, $changes);
    }

    /**
     * Scope to recent logs.
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope to specific action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get human-readable action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'update'    => 'Pengaturan Diubah',
            'reset'     => 'Reset ke Default',
            'import'    => 'Import Settings',
            'export'    => 'Export Settings',
            'schedule'  => 'Maintenance Dijadwalkan',
            default     => ucfirst($this->action),
        };
    }

    /**
     * Get formatted time.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
