<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('review_results', function (Blueprint $table) {
            // A. Informasi Naskah
            $table->string('manuscript_id')->nullable()->after('review_assignment_id');
            $table->string('manuscript_title')->nullable()->after('manuscript_id');
            $table->string('article_type')->nullable()->after('manuscript_title')->comment('Research Article or Review');
            $table->string('field_section_topic')->nullable()->after('article_type');
            
            // B. Pernyataan Konflik Kepentingan & Etika
            $table->boolean('conflict_of_interest')->default(false)->after('field_section_topic');
            $table->text('conflict_explanation')->nullable()->after('conflict_of_interest');
            $table->boolean('plagiarism_detected')->default(false)->after('conflict_explanation');
            $table->text('plagiarism_explanation')->nullable()->after('plagiarism_detected');
            $table->boolean('excessive_self_citation')->default(false)->after('plagiarism_explanation');
            $table->text('self_citation_explanation')->nullable()->after('excessive_self_citation');
            $table->boolean('other_ethical_issues')->default(false)->after('self_citation_explanation');
            $table->text('ethical_issues_explanation')->nullable()->after('other_ethical_issues');
            $table->boolean('ai_usage_statement')->default(false)->after('ethical_issues_explanation')->comment('false = tidak gunakan AI, true = gunakan AI');
            
            // C. Penilaian Cepat (Rating Umum) - 10 aspek
            $table->integer('rating_scope')->nullable()->after('ai_usage_statement')->comment('Kesesuaian dengan scope jurnal (1-5)');
            $table->text('rating_scope_note')->nullable()->after('rating_scope');
            $table->integer('rating_novelty')->nullable()->after('rating_scope_note')->comment('Kebaruan/Originalitas (1-5)');
            $table->text('rating_novelty_note')->nullable()->after('rating_novelty');
            $table->integer('rating_significance')->nullable()->after('rating_novelty_note')->comment('Signifikansi kontribusi (1-5)');
            $table->text('rating_significance_note')->nullable()->after('rating_significance');
            $table->integer('rating_soundness')->nullable()->after('rating_significance_note')->comment('Kebenaran teknis/Scientific soundness (1-5)');
            $table->text('rating_soundness_note')->nullable()->after('rating_soundness');
            $table->integer('rating_methodology')->nullable()->after('rating_soundness_note')->comment('Desain riset & metodologi (1-5)');
            $table->text('rating_methodology_note')->nullable()->after('rating_methodology');
            $table->integer('rating_analysis')->nullable()->after('rating_methodology_note')->comment('Kualitas analisis & hasil (1-5)');
            $table->text('rating_analysis_note')->nullable()->after('rating_analysis');
            $table->integer('rating_presentation')->nullable()->after('rating_analysis_note')->comment('Kualitas presentasi (1-5)');
            $table->text('rating_presentation_note')->nullable()->after('rating_presentation');
            $table->integer('rating_figures')->nullable()->after('rating_presentation_note')->comment('Kualitas gambar/tabel (1-5)');
            $table->text('rating_figures_note')->nullable()->after('rating_figures');
            $table->integer('rating_references')->nullable()->after('rating_figures_note')->comment('Kualitas referensi (1-5)');
            $table->text('rating_references_note')->nullable()->after('rating_references');
            $table->integer('rating_language')->nullable()->after('rating_references_note')->comment('Kualitas bahasa (1-5)');
            $table->text('rating_language_note')->nullable()->after('rating_language');
            
            // D. Checklist Evaluasi Detail (Ya/Tidak/Perlu Perbaikan) - 10 pertanyaan
            $table->string('checklist_abstract')->nullable()->after('rating_language_note')->comment('Ya/Tidak/Perlu Perbaikan');
            $table->string('checklist_intro')->nullable()->after('checklist_abstract');
            $table->string('checklist_novelty')->nullable()->after('checklist_intro');
            $table->string('checklist_literature')->nullable()->after('checklist_novelty');
            $table->string('checklist_method')->nullable()->after('checklist_literature');
            $table->string('checklist_design')->nullable()->after('checklist_method');
            $table->string('checklist_results')->nullable()->after('checklist_design');
            $table->string('checklist_discussion')->nullable()->after('checklist_results');
            $table->string('checklist_conclusion')->nullable()->after('checklist_discussion');
            $table->string('checklist_data_availability')->nullable()->after('checklist_conclusion');
            
            // E. Evaluasi Referensi
            $table->boolean('references_adequate')->default(true)->after('checklist_data_availability');
            $table->boolean('references_manipulation')->default(false)->after('references_adequate');
            $table->text('irrelevant_references')->nullable()->after('references_manipulation');
            $table->text('suggested_references')->nullable()->after('irrelevant_references');
            
            // F. Rekomendasi Akhir Reviewer
            $table->text('recommendation_reason')->nullable()->after('suggested_references');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_results', function (Blueprint $table) {
            $table->dropColumn([
                'manuscript_id', 'manuscript_title', 'article_type', 'field_section_topic',
                'conflict_of_interest', 'conflict_explanation', 'plagiarism_detected', 'plagiarism_explanation',
                'excessive_self_citation', 'self_citation_explanation', 'other_ethical_issues', 'ethical_issues_explanation',
                'ai_usage_statement',
                'rating_scope', 'rating_scope_note', 'rating_novelty', 'rating_novelty_note',
                'rating_significance', 'rating_significance_note', 'rating_soundness', 'rating_soundness_note',
                'rating_methodology', 'rating_methodology_note', 'rating_analysis', 'rating_analysis_note',
                'rating_presentation', 'rating_presentation_note', 'rating_figures', 'rating_figures_note',
                'rating_references', 'rating_references_note', 'rating_language', 'rating_language_note',
                'checklist_abstract', 'checklist_intro', 'checklist_novelty', 'checklist_literature',
                'checklist_method', 'checklist_design', 'checklist_results', 'checklist_discussion',
                'checklist_conclusion', 'checklist_data_availability',
                'references_adequate', 'references_manipulation', 'irrelevant_references', 'suggested_references',
                'recommendation_reason'
            ]);
        });
    }
};
