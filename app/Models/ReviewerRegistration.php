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
        'username',
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

    protected $casts = [
        'article_languages' => 'array',
    ];

    /**
     * Get the field of study associated with the registration
     */
    public function fieldOfStudy()
    {
        return $this->belongsTo(FieldOfStudy::class, 'field_of_study_id');
    }
}
