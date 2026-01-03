<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewerRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'affiliation',
        'email',
        'password',
        'scopus_id',
        'sinta_id',
        'whatsapp',
        'field_of_study',
        'field_of_study_id',
        'article_languages',
        'status',
        'notes',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'article_languages' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the field of study for this registration
     */
    public function fieldOfStudy()
    {
        return $this->belongsTo(FieldOfStudy::class, 'field_of_study_id');
    }
}
