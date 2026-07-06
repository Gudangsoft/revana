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
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'total_points'      => 'float',
        'tanggal_lahir'     => 'date',
        'additional_phones' => 'array',
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
     * Get real-time point count (1 submission = 1 point)
     */
    public function getActualPoints(): int
    {
        return $this->submissions()->count();
    }

    /**
     * Sync total_points column with actual submission count
     */
    public function syncPoints(): int
    {
        $actualPoints = $this->getActualPoints();
        // total_points is cast as float; compare loosely to avoid false positives
        if ((int) round($this->total_points) !== $actualPoints) {
            $this->update(['total_points' => $actualPoints]);
        }
        return $actualPoints;
    }
}

