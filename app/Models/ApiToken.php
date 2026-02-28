<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'app_identifier',
        'token',
        'token_plain',
        'permissions',
        'allowed_ips',
        'rate_limit',
        'expires_at',
        'last_used_at',
        'total_requests',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'permissions'    => 'array',
        'is_active'      => 'boolean',
        'expires_at'     => 'datetime',
        'last_used_at'   => 'datetime',
        'total_requests' => 'integer',
        'rate_limit'     => 'integer',
    ];

    protected $hidden = [
        'token',
        'token_plain',
    ];

    // ─── Daftar permission yang tersedia ────────────────────────────────────
    public static array $availablePermissions = [
        'journals'        => 'Data Master Jurnal',
        'accreditations'  => 'Data Akreditasi',
        'field_of_study'  => 'Data Bidang Studi',
        'kategori'        => 'Data Kategori',
        'jenis_jurnal'    => 'Data Jenis Jurnal',
        'submissions'     => 'Data Submission (read-only)',
    ];

    // ─── Generate token baru ─────────────────────────────────────────────────
    public static function generateToken(): array
    {
        $plain = 'sipera_' . Str::random(48);
        $hashed = hash('sha256', $plain);
        return ['plain' => $plain, 'hashed' => $hashed];
    }

    // ─── Cari token dari raw value ────────────────────────────────────────────
    public static function findByRawToken(string $rawToken): ?self
    {
        $hashed = hash('sha256', $rawToken);
        return self::where('token', $hashed)->where('is_active', true)->first();
    }

    // ─── Validasi apakah token masih bisa digunakan ───────────────────────────
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }

    // ─── Cek apakah punya permission tertentu ─────────────────────────────────
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) return true; // null = akses semua
        return in_array($permission, $this->permissions);
    }

    // ─── Cek apakah IP diizinkan ─────────────────────────────────────────────
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->allowed_ips)) return true;
        $allowed = array_map('trim', explode(',', $this->allowed_ips));
        return in_array($ip, $allowed);
    }

    // ─── Rekam pemakaian ─────────────────────────────────────────────────────
    public function recordUsage(): void
    {
        $this->timestamps = false;
        $this->update([
            'last_used_at'   => now(),
            'total_requests' => $this->total_requests + 1,
        ]);
        $this->timestamps = true;
    }

    // ─── Relasi ──────────────────────────────────────────────────────────────
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
