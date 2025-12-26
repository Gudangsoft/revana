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
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function approve()
    {
        $this->update([
            'status' => 'APPROVED',
            'approved_at' => now(),
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);
    }

    public function reject($adminNotes)
    {
        $this->update([
            'status' => 'REJECTED',
            'admin_notes' => $adminNotes,
        ]);

        // Return points to user
        $this->user->increment('available_points', $this->points_used);

        // Create point history
        PointHistory::create([
            'user_id' => $this->user_id,
            'points' => $this->points_used,
            'type' => 'EARNED',
            'description' => "Pengembalian point dari penukaran reward yang ditolak: {$this->reward->name}",
        ]);
    }
}
