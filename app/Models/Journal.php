<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot',
        'volume',
        'title',
        'link',
        'accreditation',
        'points',
        'status',
        'created_by',
        'pic_author_id',
        'pic_marketing_id',
        'pic_editor_id',
        'publisher',
        'marketing',
        'pic',
        'author_username',
        'author_password',
        'turnitin_link',
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
        // Try to get points from accreditations table
        $accreditationModel = Accreditation::where('name', $accreditation)->first();
        
        if ($accreditationModel) {
            return $accreditationModel->points;
        }

        // Fallback to hardcoded values if not found in database
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

    public function accreditationModel()
    {
        return $this->belongsTo(Accreditation::class, 'accreditation', 'name');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewAssignments()
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function assignments()
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function picAuthor()
    {
        return $this->belongsTo(Pic::class, 'pic_author_id');
    }

    public function picMarketing()
    {
        return $this->belongsTo(Marketing::class, 'pic_marketing_id');
    }

    public function picEditor()
    {
        return $this->belongsTo(Pic::class, 'pic_editor_id');
    }
}
