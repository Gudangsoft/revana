<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_assignment_id',
        'file_path',
        'notes',
        'recommendation',
        'admin_feedback',
        // Basic Information (Old)
        'journal_name',
        'article_code',
        'article_title',
        'reviewer_name',
        'review_date',
        // A. Informasi Naskah (New SIPERA)
        'manuscript_id',
        'manuscript_title',
        'article_type',
        'field_section_topic',
        // B. Pernyataan Konflik Kepentingan & Etika
        'conflict_of_interest',
        'conflict_explanation',
        'plagiarism_detected',
        'plagiarism_explanation',
        'excessive_self_citation',
        'self_citation_explanation',
        'other_ethical_issues',
        'ethical_issues_explanation',
        'ai_usage_statement',
        // C. Penilaian Cepat (10 aspek)
        'rating_scope', 'rating_scope_note',
        'rating_novelty', 'rating_novelty_note',
        'rating_significance', 'rating_significance_note',
        'rating_soundness', 'rating_soundness_note',
        'rating_methodology', 'rating_methodology_note',
        'rating_analysis', 'rating_analysis_note',
        'rating_presentation', 'rating_presentation_note',
        'rating_figures', 'rating_figures_note',
        'rating_references', 'rating_references_note',
        'rating_language', 'rating_language_note',
        // D. Checklist Evaluasi Detail (10 pertanyaan)
        'checklist_abstract',
        'checklist_intro',
        'checklist_novelty',
        'checklist_literature',
        'checklist_method',
        'checklist_design',
        'checklist_results',
        'checklist_discussion',
        'checklist_conclusion',
        'checklist_data_availability',
        // E. Evaluasi Referensi
        'references_adequate',
        'references_manipulation',
        'irrelevant_references',
        'suggested_references',
        // F. Rekomendasi Akhir
        'recommendation_reason',
        // Old fields (kept for backward compatibility)
        'score_1', 'comment_1',
        'score_2', 'comment_2',
        'score_3', 'comment_3',
        'score_4', 'comment_4',
        'score_5', 'comment_5',
        'score_6', 'comment_6',
        'score_7', 'comment_7',
        'score_8', 'comment_8',
        'technical_1',
        'technical_2',
        'technical_3',
        'improvement_suggestions',
        'reviewer_signature',
        'statement_date',
    ];

    protected $casts = [
        'review_date' => 'date',
        'statement_date' => 'date',
        'technical_1' => 'boolean',
        'technical_2' => 'boolean',
        'technical_3' => 'boolean',
        'conflict_of_interest' => 'boolean',
        'plagiarism_detected' => 'boolean',
        'excessive_self_citation' => 'boolean',
        'other_ethical_issues' => 'boolean',
        'ai_usage_statement' => 'boolean',
        'references_adequate' => 'boolean',
        'references_manipulation' => 'boolean',
    ];

    public function reviewAssignment()
    {
        return $this->belongsTo(ReviewAssignment::class);
    }
}
