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
        'photo',
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

    /**
     * Get total pending/unfinished tasks count
     * A task is pending when PIC is assigned (petugas_X_id) but X_valid is still false/null
     */
    public function getPendingTasksCountAttribute()
    {
        $picId = $this->id;
        $steps = [
            ['field' => 'petugas_submit_id',     'valid' => null], // submit has no valid field
            ['field' => 'petugas_editor1_id',     'valid' => 'editor1_valid'],
            ['field' => 'petugas_author1_id',     'valid' => 'author1_valid'],
            ['field' => 'petugas_editor2_id',     'valid' => 'editor2_valid'],
            ['field' => 'petugas_reviewer1_id',   'valid' => 'reviewer1_valid'],
            ['field' => 'petugas_reviewer2_id',   'valid' => 'reviewer2_valid'],
            ['field' => 'petugas_editor3_id',     'valid' => 'editor3_valid'],
            ['field' => 'petugas_author2_id',     'valid' => 'author2_valid'],
            ['field' => 'petugas_production_id',  'valid' => 'production_valid'],
        ];

        $count = 0;
        foreach ($steps as $step) {
            $query = Submission::where($step['field'], $picId)
                ->where('status', '!=', 'PUBLISHED')
                ->where('status', '!=', 'REJECTED');
            if ($step['valid']) {
                $query->where(function ($q) use ($step) {
                    $q->whereNull($step['valid'])->orWhere($step['valid'], false);
                });
            }
            $count += $query->count();
        }

        return $count;
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
