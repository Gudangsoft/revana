<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldOfStudy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get reviewers with this field of study
     */
    public function users()
    {
        return $this->hasMany(User::class, 'field_of_study_id');
    }

    /**
     * Get reviewer registrations with this field of study
     */
    public function reviewerRegistrations()
    {
        return $this->hasMany(ReviewerRegistration::class, 'field_of_study_id');
    }

    /**
     * Scope to get only active fields
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by custom order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}
