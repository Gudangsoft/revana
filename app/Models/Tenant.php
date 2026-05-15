<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'institution', 'email', 'phone',
        'subdomain', 'custom_domain',
        'db_name', 'db_user', 'db_password',
        'features', 'branding', 'plan', 'status',
        'trial_ends_at', 'expires_at',
        'admin_name', 'admin_email', 'notes',
    ];

    protected $casts = [
        'features'      => 'array',
        'branding'      => 'array',
        'trial_ends_at' => 'datetime',
        'expires_at'    => 'datetime',
    ];

    // ─── Feature helpers ────────────────────────────────────────────────────

    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        if (array_key_exists($feature, $features)) {
            return (bool) $features[$feature];
        }
        // Fallback ke default dari config
        return (bool) (config("tenants.features.{$feature}.default") ?? false);
    }

    public function enableFeature(string $feature): void
    {
        $features = $this->features ?? [];
        $features[$feature] = true;
        $this->update(['features' => $features]);
    }

    public function disableFeature(string $feature): void
    {
        $features = $this->features ?? [];
        $features[$feature] = false;
        $this->update(['features' => $features]);
    }

    public function toggleFeature(string $feature): bool
    {
        $current = $this->hasFeature($feature);
        $features = $this->features ?? [];
        $features[$feature] = !$current;
        $this->update(['features' => $features]);
        return !$current;
    }

    // Inisialisasi fitur berdasarkan plan
    public function initFeaturesFromPlan(): void
    {
        $planFeatures = config("tenants.plans.{$this->plan}.features", []);
        $all = array_keys(config('tenants.features', []));
        $features = [];
        foreach ($all as $key) {
            $features[$key] = in_array($key, $planFeatures);
        }
        $this->update(['features' => $features]);
    }

    // ─── Status helpers ─────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isExpired(): bool
    {
        if ($this->plan === 'lifetime') return false;
        if ($this->status === 'expired') return true;
        if ($this->expires_at && $this->expires_at->isPast()) return true;
        if ($this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast()) return true;
        return false;
    }

    public function daysLeft(): ?int
    {
        if ($this->plan === 'lifetime') return null; // null = seumur hidup
        $date = $this->status === 'trial' ? $this->trial_ends_at : $this->expires_at;
        if (!$date) return null;
        return max(0, (int) now()->diffInDays($date, false));
    }

    // ─── Domain helpers ──────────────────────────────────────────────────────

    public function getHostAttribute(): string
    {
        if ($this->custom_domain) return $this->custom_domain;
        $base = config('tenants.master_domain');
        // Ambil bagian domain tanpa subdomain master (apji.org)
        $parts = explode('.', $base);
        array_shift($parts);
        return $this->subdomain . '.' . implode('.', $parts);
    }

    public function getUrlAttribute(): string
    {
        return 'https://' . $this->host;
    }

    // ─── Status badge ────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'    => '<span class="badge bg-success">Aktif</span>',
            'trial'     => '<span class="badge bg-info text-white">Trial</span>',
            'suspended' => '<span class="badge bg-danger">Suspended</span>',
            'expired'   => '<span class="badge bg-secondary">Expired</span>',
            default     => '<span class="badge bg-light text-dark">' . $this->status . '</span>',
        };
    }
}
