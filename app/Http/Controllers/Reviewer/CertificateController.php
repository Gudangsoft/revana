<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CertificateController extends Controller
{
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

        // Ambil template sertifikat
        $templatePath = Setting::get('certificate_template');
        
        if (!$templatePath || !Storage::disk('public')->exists($templatePath)) {
            return back()->with('error', 'Template sertifikat belum tersedia');
        }

        $fullPath = Storage::disk('public')->path($templatePath);
        
        // Ambil data reviewer
        $reviewer = auth()->user();
        $reviewerName = $reviewer->name;
        $articleTitle = $assignment->article_title;
        $completedDate = $assignment->approved_at ? $assignment->approved_at->format('d F Y') : now()->format('d F Y');
        
        // Load image
        $img = Image::make($fullPath);
        
        // Tambahkan teks pada gambar (sesuaikan koordinat dan font)
        // Nama Reviewer
        $img->text($reviewerName, $img->width() / 2, $img->height() / 2 - 100, function($font) {
            $font->file(public_path('fonts/Arial-Bold.ttf'));
            $font->size(48);
            $font->color('#000000');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Judul Artikel
        $img->text('Review: ' . \Illuminate\Support\Str::limit($articleTitle, 60), $img->width() / 2, $img->height() / 2, function($font) {
            $font->file(public_path('fonts/Arial.ttf'));
            $font->size(24);
            $font->color('#333333');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Tanggal
        $img->text($completedDate, $img->width() / 2, $img->height() / 2 + 100, function($font) {
            $font->file(public_path('fonts/Arial.ttf'));
            $font->size(20);
            $font->color('#666666');
            $font->align('center');
            $font->valign('middle');
        });
        
        // Generate filename
        $filename = 'Certificate_' . str_replace(' ', '_', $reviewerName) . '_' . $assignment->id . '.jpg';
        
        // Return as download
        return $img->response('jpg', 90, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
