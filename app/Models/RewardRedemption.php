<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'points_used',
        'status',
        'notes',
        'admin_notes',
        'approved_at',
        'completed_at',
        'proof_type',
        'proof_number',
        'proof_date',
    ];

    protected $casts = [
        'points_used' => 'integer',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'proof_date' => 'date',
    ];

    /**
     * Get the user that owns the redemption.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reward being redeemed.
     */
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Scope to get pending redemptions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope to get approved redemptions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    /**
     * Scope to get completed redemptions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    /**
     * Scope to get rejected redemptions.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }
}
