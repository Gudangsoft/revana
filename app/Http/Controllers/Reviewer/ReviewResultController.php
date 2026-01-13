<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use App\Models\ReviewResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ReviewResultController extends Controller
{
    public function create(ReviewAssignment $assignment)
    {
        $isReviewer = $assignment->reviewer_id === auth()->id() 
                   || $assignment->reviewer_2_id === auth()->id()
                   || $assignment->reviewer_3_id === auth()->id()
                   || $assignment->reviewer_4_id === auth()->id()
                   || $assignment->reviewer_5_id === auth()->id();
                   
        if (!$isReviewer) {
            abort(403);
        }

        if (!in_array($assignment->status, ['ON_PROGRESS', 'REVISION'])) {
            return back()->with('error', 'Review belum dimulai');
        }
        
        // Check if expired
        if ($assignment->isExpired()) {
            return back()->with('error', 'Task sudah melewati deadline dan tidak dapat dikerjakan lagi');
        }

        return view('reviewer.results.create', compact('assignment'));
    }

    public function store(Request $request, ReviewAssignment $assignment)
    {
        $isReviewer = $assignment->reviewer_id === auth()->id() 
                   || $assignment->reviewer_2_id === auth()->id()
                   || $assignment->reviewer_3_id === auth()->id()
                   || $assignment->reviewer_4_id === auth()->id()
                   || $assignment->reviewer_5_id === auth()->id();
                   
        if (!$isReviewer) {
            abort(403);
        }
        
        // Check if expired
        if ($assignment->isExpired()) {
            return back()->with('error', 'Task sudah melewati deadline dan tidak dapat disubmit lagi');
        }

        $validated = $request->validate([
            // A. Informasi Naskah
            'manuscript_id' => 'required|string|max:255',
            'manuscript_title' => 'required|string',
            'article_type' => 'required|in:Research Article,Review',
            'field_section_topic' => 'required|string|max:255',
            'review_date' => 'required|date',
            
            // B. Pernyataan Konflik Kepentingan & Etika
            'conflict_of_interest' => 'required|boolean',
            'conflict_explanation' => 'nullable|string',
            'plagiarism_detected' => 'required|boolean',
            'plagiarism_explanation' => 'nullable|string',
            'excessive_self_citation' => 'required|boolean',
            'self_citation_explanation' => 'nullable|string',
            'other_ethical_issues' => 'required|boolean',
            'ethical_issues_explanation' => 'nullable|string',
            'ai_usage_statement' => 'required|boolean',
            
            // C. Penilaian Cepat (Rating Umum) - 10 aspek
            'rating_scope' => 'required|integer|min:1|max:5',
            'rating_scope_note' => 'nullable|string',
            'rating_novelty' => 'required|integer|min:1|max:5',
            'rating_novelty_note' => 'nullable|string',
            'rating_significance' => 'required|integer|min:1|max:5',
            'rating_significance_note' => 'nullable|string',
            'rating_soundness' => 'required|integer|min:1|max:5',
            'rating_soundness_note' => 'nullable|string',
            'rating_methodology' => 'required|integer|min:1|max:5',
            'rating_methodology_note' => 'nullable|string',
            'rating_analysis' => 'required|integer|min:1|max:5',
            'rating_analysis_note' => 'nullable|string',
            'rating_presentation' => 'required|integer|min:1|max:5',
            'rating_presentation_note' => 'nullable|string',
            'rating_figures' => 'required|integer|min:1|max:5',
            'rating_figures_note' => 'nullable|string',
            'rating_references' => 'required|integer|min:1|max:5',
            'rating_references_note' => 'nullable|string',
            'rating_language' => 'required|integer|min:1|max:5',
            'rating_language_note' => 'nullable|string',
            
            // D. Checklist Evaluasi Detail - 10 pertanyaan
            'checklist_abstract' => 'required|in:Ya,Tidak,Perlu Perbaikan',
            'checklist_intro' => 'required|in:Ya,Tidak,Perlu Perbaikan',
            'checklist_novelty' => 'required|in:Ya,Tidak,Perlu Perbaikan',
            'checklist_literature' => 'required|in:Ya,Tidak,Perlu Perbaikan',
            'checklist_method' => 'required|in:Ya,Tidak,Perlu Perbaikan',
            'checklist_design' => 'required|in:Ya,Tidak,Perlu Perbaikan',
            'checklist_results' => 'required|in:Ya,Tidak,Perlu Perbaikan',
            'checklist_discussion' => 'required|in:Ya,Tidak,Perlu Perbaikan',
            'checklist_conclusion' => 'required|in:Ya,Tidak,Perlu Perbaikan',
            'checklist_data_availability' => 'required|in:Ya,Tidak,Perlu Perbaikan',
            
            // E. Evaluasi Referensi
            'references_adequate' => 'required|boolean',
            'suggested_references' => 'nullable|string',
            
            // F. Rekomendasi Akhir Reviewer
            'recommendation' => 'required|in:ACCEPT,MINOR_REVISION,MAJOR_REVISION,REJECT_RESUBMIT,REJECT',
            'recommendation_reason' => 'required|string',
        ]);

        // Auto-fill reviewer name from authenticated user
        $validated['reviewer_name'] = auth()->user()->name;
        $validated['reviewer_signature'] = auth()->user()->name;
        $validated['statement_date'] = $validated['review_date'];

        // Keep old file_path for backward compatibility (empty or placeholder)
        $validated['file_path'] = 'formulir-review-sipera';

        // Add reviewer_id
        $validated['reviewer_id'] = auth()->id();

        // Create or update review result for this specific reviewer
        ReviewResult::updateOrCreate(
            [
                'review_assignment_id' => $assignment->id,
                'reviewer_id' => auth()->id()
            ],
            $validated
        );

        // Update assignment status
        $assignment->submit();

        // Redirect to download PDF directly
        return redirect()->route('reviewer.results.downloadPdf', $assignment)
            ->with('success', 'Formulir review berhasil disubmit! File PDF akan otomatis terdownload.');
    }

    public function downloadPdf(ReviewAssignment $assignment)
    {
        // Check authorization
        $isReviewer = $assignment->reviewer_id === auth()->id() 
                   || $assignment->reviewer_2_id === auth()->id()
                   || $assignment->reviewer_3_id === auth()->id()
                   || $assignment->reviewer_4_id === auth()->id()
                   || $assignment->reviewer_5_id === auth()->id();
                   
        if (!$isReviewer) {
            abort(403);
        }

        // Get review result for current reviewer
        $result = $assignment->reviewResults()->where('reviewer_id', auth()->id())->first();
        if (!$result) {
            return back()->with('error', 'Data review tidak ditemukan. Silakan isi formulir review terlebih dahulu.');
        }

        // Get reviewer data for signature
        $reviewer = auth()->user();

        // Generate PDF
        $pdf = Pdf::loadView('reviewer.results.pdf', [
            'result' => $result,
            'reviewer' => $reviewer,
            'assignment' => $assignment
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Download PDF
        $filename = 'Review_' . $result->article_code . '_' . date('YmdHis') . '.pdf';
        return $pdf->download($filename);
    }

    public function uploadRevision(Request $request, ReviewAssignment $assignment)
    {
        // Check authorization
        $isReviewer = $assignment->reviewer_id === auth()->id() 
                   || $assignment->reviewer_2_id === auth()->id()
                   || $assignment->reviewer_3_id === auth()->id()
                   || $assignment->reviewer_4_id === auth()->id()
                   || $assignment->reviewer_5_id === auth()->id();
                   
        if (!$isReviewer) {
            abort(403);
        }

        // Validate file
        $request->validate([
            'revision_file' => 'required|file|mimes:pdf,doc,docx|max:10240', // Max 10MB
        ]);

        // Get review result for current reviewer
        $result = $assignment->reviewResults()->where('reviewer_id', auth()->id())->first();
        if (!$result) {
            return back()->with('error', 'Anda harus mengisi formulir review terlebih dahulu');
        }

        // Delete old file if exists
        if ($result->revision_file) {
            Storage::delete($result->revision_file);
        }

        // Store new file
        $path = $request->file('revision_file')->store('review-revisions', 'public');
        
        // Update review result
        $result->update([
            'revision_file' => $path
        ]);

        return redirect()->route('reviewer.tasks.show', $assignment)
            ->with('success', 'File revisi jurnal berhasil diupload! Menunggu validasi admin.');
    }
}
