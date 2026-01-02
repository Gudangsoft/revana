<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'total_points',
        'available_points',
        'completed_reviews',
        'phone',
        'institution',
        'position',
        'education_level',
        'specialization',
        'address',
        'nidn',
        'google_scholar',
        'sinta_id',
        'scopus_id',
        'bio',
        'photo',
        'article_languages',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'total_points' => 'integer',
        'available_points' => 'integer',
        'completed_reviews' => 'integer',
        'article_languages' => 'array',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isReviewer()
    {
        return $this->role === 'reviewer';
    }

    public function journals()
    {
        return $this->hasMany(Journal::class, 'created_by');
    }

    public function reviewAssignments()
    {
        return $this->hasMany(ReviewAssignment::class, 'reviewer_id');
    }

    public function assignedReviews()
    {
        return $this->hasMany(ReviewAssignment::class, 'assigned_by');
    }

    public function pointHistories()
    {
        return $this->hasMany(PointHistory::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withTimestamps()
            ->withPivot('earned_at');
    }

    public function rewardRedemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function checkAndAwardBadges()
    {
        $completedReviews = $this->completed_reviews;
        $badges = Badge::where('required_reviews', '<=', $completedReviews)->get();

        foreach ($badges as $badge) {
            if (!$this->badges->contains($badge->id)) {
                $this->badges()->attach($badge->id, ['earned_at' => now()]);
            }
        }
    }
}
