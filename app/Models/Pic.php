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
        'tanggal_lahir',
        'is_active',
        'total_points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'password'      => 'hashed',
        'total_points'  => 'float',
        'tanggal_lahir' => 'date',
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
     * Get total pending/unfinished tasks count.
     * A task is pending when:
     *  - the PIC is assigned to that step (petugas_X_id = this PIC)
     *  - the submission is currently AT that step (status = X_PROCESS)
     *  - the step is not yet validated (X_valid is null or false)
     *
     * petugas_submit_id is excluded â€” submission is a one-time action, not a validatable task.
     */
    public function getPendingTasksCountAttribute()
    {
        $picId = $this->id;
        $steps = [
            ['field' => 'petugas_editor1_id',    'valid' => 'editor1_valid',    'status' => 'EDITOR1_PROCESS'],
            ['field' => 'petugas_author1_id',     'valid' => 'author1_valid',    'status' => 'AUTHOR1_PROCESS'],
            ['field' => 'petugas_editor2_id',     'valid' => 'editor2_valid',    'status' => 'EDITOR2_PROCESS'],
            ['field' => 'petugas_reviewer1_id',   'valid' => 'reviewer1_valid',  'status' => 'REVIEWER1_PROCESS'],
            ['field' => 'petugas_reviewer2_id',   'valid' => 'reviewer2_valid',  'status' => 'REVIEWER2_PROCESS'],
            ['field' => 'petugas_editor3_id',     'valid' => 'editor3_valid',    'status' => 'EDITOR3_PROCESS'],
            ['field' => 'petugas_author2_id',     'valid' => 'author2_valid',    'status' => 'AUTHOR2_PROCESS'],
            ['field' => 'petugas_production_id',  'valid' => 'production_valid', 'status' => 'PRODUCTION_PROCESS'],
        ];

        $count = 0;
        foreach ($steps as $step) {
            $count += Submission::where($step['field'], $picId)
                ->where('status', $step['status'])
                ->where(function ($q) use ($step) {
                    $q->whereNull($step['valid'])->orWhere($step['valid'], false);
                })
                ->count();
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

