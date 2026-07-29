<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Marketing extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'username',
        'email',
        'photo',
        'phone',
        'additional_phones',
        'tanggal_lahir',
        'password',
        'is_active',
        'total_points',
        'points_reset_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'total_points'      => 'float',
        'tanggal_lahir'     => 'date',
        'additional_phones' => 'array',
        'points_reset_at'   => 'datetime',
    ];

    public function isBirthdayToday(): bool
    {
        if (!$this->tanggal_lahir) return false;
        return $this->tanggal_lahir->month === now()->month
            && $this->tanggal_lahir->day === now()->day;
    }

    public function getUmurAttribute(): ?int
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : null;
    }

    /**
     * Submissions yang dibawa oleh marketing ini
     */
    public function submissions()
    {
        return $this->hasMany(Submission::class, 'marketing_id');
    }

    /**
     * Point histories
     */
    public function pointHistories()
    {
        return $this->hasMany(MarketingPointHistory::class);
    }

    /**
     * Get real-time point total — SUM riwayat poin, BUKAN COUNT submission. Poin per
     * submission mengikuti TaskPointSetting yang berlaku SAAT poin itu diberikan, jadi
     * kalau rate-nya pernah diubah, COUNT submission tidak akan cocok lagi dengan SUM
     * riwayat yang sebenarnya (lihat MarketingPointHistory::awardPoints()).
     */
    public function getActualPoints(): float
    {
        return (float) $this->pointHistories()->sum('points_earned');
    }

    /**
     * Sync total_points column dari SUM riwayat poin yang sebenarnya.
     */
    public function syncPoints(): float
    {
        $actualPoints = $this->getActualPoints();
        // round() ke 4 desimal untuk hindari false positive akibat presisi float
        if (round((float) $this->total_points, 4) !== round($actualPoints, 4)) {
            $this->update(['total_points' => $actualPoints]);
        }
        return $actualPoints;
    }
}

