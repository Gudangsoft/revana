<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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

        // Get active certificate template
        $template = Certificate::where('is_active', true)->first();
        
        if (!$template) {
            return back()->with('error', 'Template sertifikat belum tersedia');
        }

        $templatePath = storage_path('app/public/' . $template->file_path);
        
        if (!file_exists($templatePath)) {
            return back()->with('error', 'File template tidak ditemukan');
        }

        $reviewer = auth()->user();
        
        // Create image manager
        $manager = new ImageManager(new Driver());
        
        // Load template image
        $image = $manager->read($templatePath);
        
        // Get image dimensions
        $width = $image->width();
        $height = $image->height();
        
        // Prepare text data
        $year = $assignment->approved_at->format('Y');
        $date = $assignment->approved_at->format('d');
        $month = $assignment->approved_at->locale('id')->translatedFormat('F');
        $reviewerName = strtoupper($reviewer->name);
        $articleTitle = $assignment->article_title;
        
        // Wrap long article title
        $wrappedTitle = wordwrap($articleTitle, 100, "\n");
        
        // Template size: 2560x1811px
        // Positions calculated based on template layout
        
        // Add year (top right) - "2026"
        $image->text($year, $width - 340, 230, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(165);
            $font->color('#C9A961');
            $font->align('right');
        });
        
        // Add date (below year) - "03 Januari"
        $image->text("$date $month", $width - 465, 375, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(55);
            $font->color('#C9A961');
            $font->align('right');
        });
        
        // Add reviewer name (center) - "EKO SISWANTO, M.KOM"
        $image->text($reviewerName, $width / 2, 770, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(95);
            $font->color('#C9A961');
            $font->align('center');
        });
        
        // Add article title (center, below name) - Judul artikel
        $image->text($wrappedTitle, $width / 2, 1045, function($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(48);
            $font->color('#C9A961');
            $font->align('center');
        });
        
        // Save to temporary file
        $tempFilename = 'certificate_' . time() . '_' . $assignment->id . '.jpg';
        $tempPath = storage_path('app/public/temp/' . $tempFilename);
        
        // Create temp directory if not exists
        if (!file_exists(storage_path('app/public/temp'))) {
            mkdir(storage_path('app/public/temp'), 0755, true);
        }
        
        // Encode and save
        $image->toJpeg(95)->save($tempPath);
        
        // Download file
        $filename = 'Sertifikat_' . str_replace(' ', '_', $reviewer->name) . '_' . $assignment->article_number . '.jpg';
        
        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
