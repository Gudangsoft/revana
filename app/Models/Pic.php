<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pic extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'role',
        'email',
        'password',
        'phone',
        'is_active',
        'total_points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed',
        'total_points' => 'integer',
    ];

    /**
     * Relationship to point histories
     */
    public function pointHistories()
    {
        return $this->hasMany(PicPointHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get points earned this month
     */
    public function getPointsThisMonthAttribute()
    {
        return $this->pointHistories()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('points_earned');
    }

    /**
     * Get points earned today
     */
    public function getPointsTodayAttribute()
    {
        return $this->pointHistories()
            ->whereDate('created_at', now()->toDateString())
            ->sum('points_earned');
    }

    /**
     * Get total tasks completed (count of point histories)
     */
    public function getTotalTasksCompletedAttribute()
    {
        return $this->pointHistories()->count();
    }

    public function isAuthor()
    {
        return $this->role === 'AUTOR 1';
    }

    public function isEditor()
    {
        return $this->role === 'EDITOR 1';
    }

    public function isReviewer()
    {
        return in_array($this->role, ['REVIEWER 1', 'REVIEWER 2']);
    }
}
