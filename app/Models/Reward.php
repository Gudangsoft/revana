<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'points_required',
        'value',
        'is_active',
        'tier',
    ];

    protected $casts = [
        'points_required' => 'integer',
        'value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the redemptions for this reward.
     */
    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Scope to get only active rewards.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get rewards by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
