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
            // Basic Information
            $table->string('journal_name')->nullable()->after('review_assignment_id');
            $table->string('article_code')->nullable()->after('journal_name');
            $table->string('article_title')->nullable()->after('article_code');
            $table->string('reviewer_name')->nullable()->after('article_title');
            $table->date('review_date')->nullable()->after('reviewer_name');
            
            // Section I: Penilaian Substansi Artikel (8 aspek)
            $table->integer('score_1')->nullable()->comment('Kebaruan dan relevansi topik penelitian (1-5)');
            $table->text('comment_1')->nullable();
            $table->integer('score_2')->nullable()->comment('Kesesuaian judul dengan isi artikel (1-5)');
            $table->text('comment_2')->nullable();
            $table->integer('score_3')->nullable()->comment('Kejelasan latar belakang dan rumusan masalah (1-5)');
            $table->text('comment_3')->nullable();
            $table->integer('score_4')->nullable()->comment('Kejelasan tujuan dan kontribusi penelitian (1-5)');
            $table->text('comment_4')->nullable();
            $table->integer('score_5')->nullable()->comment('Ketepatan metode dan pendekatan penelitian (1-5)');
            $table->text('comment_5')->nullable();
            $table->integer('score_6')->nullable()->comment('Kualitas analisis dan pembahasan (1-5)');
            $table->text('comment_6')->nullable();
            $table->integer('score_7')->nullable()->comment('Kualitas hasil penelitian (1-5)');
            $table->text('comment_7')->nullable();
            $table->integer('score_8')->nullable()->comment('Kejelasan simpulan dan implikasi penelitian (1-5)');
            $table->text('comment_8')->nullable();
            
            // Section II: Penilaian Teknis Penulisan (3 kriteria)
            $table->boolean('technical_1')->default(false)->comment('Artikel mengikuti format dan sistematika jurnal');
            $table->boolean('technical_2')->default(false)->comment('Bahasa dan tata tulis sesuai kaidah ilmiah');
            $table->boolean('technical_3')->default(false)->comment('Referensi memadai dan terkini');
            
            // Section III: Saran Perbaikan
            $table->text('improvement_suggestions')->nullable();
            
            // Section IV: Rekomendasi (sudah ada di kolom recommendation)
            
            // Section V: Pernyataan Reviewer
            $table->string('reviewer_signature')->nullable()->comment('Nama lengkap reviewer untuk pernyataan');
            $table->date('statement_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_results', function (Blueprint $table) {
            $table->dropColumn([
                'journal_name', 'article_code', 'article_title', 'reviewer_name', 'review_date',
                'score_1', 'comment_1', 'score_2', 'comment_2', 'score_3', 'comment_3',
                'score_4', 'comment_4', 'score_5', 'comment_5', 'score_6', 'comment_6',
                'score_7', 'comment_7', 'score_8', 'comment_8',
                'technical_1', 'technical_2', 'technical_3',
                'improvement_suggestions', 'reviewer_signature', 'statement_date'
            ]);
        });
    }
};
