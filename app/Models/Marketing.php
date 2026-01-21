<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Marketing extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'total_points',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_points' => 'integer',
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
}
