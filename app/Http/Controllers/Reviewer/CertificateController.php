<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get all approved assignments for this reviewer
        $assignments = ReviewAssignment::where(function($query) use ($user) {
                $query->where('reviewer_id', $user->id)
                      ->orWhere('reviewer_2_id', $user->id);
            })
            ->where('status', 'APPROVED')
            ->with(['reviewer', 'reviewer2'])
            ->latest()
            ->get();
        
        // Get active certificate templates
        $templates = Certificate::where('is_active', true)->get();
        
        return view('reviewer.certificates.index', compact('assignments', 'templates'));
    }

    public function download(ReviewAssignment $assignment)
    {
        // Cek apakah user adalah reviewer dari assignment ini
        if ($assignment->reviewer_id != auth()->id() && $assignment->reviewer2_id != auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Cek apakah review sudah approved
        if ($assignment->status !== 'APPROVED') {
            return back()->with('error', 'Sertifikat hanya tersedia untuk review yang sudah disetujui');
        }

        // Jika ada certificate_link yang diupload khusus, download itu
        if ($assignment->certificate_link) {
            $filePath = storage_path('app/public/' . $assignment->certificate_link);
            if (file_exists($filePath)) {
                return response()->download($filePath);
            }
        }

        // Jika tidak ada, ambil template aktif pertama
        $template = Certificate::where('is_active', true)->first();
        
        if (!$template) {
            return back()->with('error', 'Template sertifikat belum tersedia');
        }

        $filePath = storage_path('app/public/' . $template->file_path);
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'File sertifikat tidak ditemukan');
        }

        $reviewer = auth()->user();
        $filename = 'Sertifikat_' . str_replace(' ', '_', $reviewer->name) . '_' . $assignment->article_number . '.jpg';
        
        return response()->download($filePath, $filename);
    }
}
