<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link',
        'accreditation',
        'points',
        'created_by',
        'publisher',
        'marketing',
        'pic',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journal) {
            $journal->points = self::calculatePoints($journal->accreditation);
        });

        static::updating(function ($journal) {
            if ($journal->isDirty('accreditation')) {
                $journal->points = self::calculatePoints($journal->accreditation);
            }
        });
    }

    public static function calculatePoints($accreditation)
    {
        return match($accreditation) {
            'SINTA 1' => 100,
            'SINTA 2' => 80,
            'SINTA 3' => 60,
            'SINTA 4' => 40,
            'SINTA 5' => 20,
            'SINTA 6' => 10,
            default => 0,
        };
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewAssignments()
    {
        return $this->hasMany(ReviewAssignment::class);
    }
}
