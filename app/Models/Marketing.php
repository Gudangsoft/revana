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
        'password',
        'is_active',
        'total_points',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'total_points' => 'float',
    ];

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
        if ($this->total_points !== $actualPoints) {
            $this->update(['total_points' => $actualPoints]);
        }
        return $actualPoints;
    }
}
