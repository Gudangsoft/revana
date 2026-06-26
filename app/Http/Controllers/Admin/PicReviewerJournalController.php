<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use App\Models\Pic;
use App\Models\Submission;
use Illuminate\Http\Request;

class PicReviewerJournalController extends Controller
{
    public function monitoring(Request $request)
    {
        $program = $request->input('program', '');

        $query = Submission::with([
            'journalSlot.journalMaster',
            'marketing',
            'petugasSubmit',
            'petugasReviewer1',
            'petugasReviewer2',
        ])
        ->where(fn($q) =>
            $q->where('process_type', '!=', 'fasttrack')->orWhereNull('process_type')
        );

        // Program filter
        if ($program === 'bkd') {
            $query->where('program_type', 'bkd');
        } elseif ($program === 'jafa') {
            $query->where('program_type', 'jafa');
        } else {
            $query->whereNull('program_type');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('journal_master_id')) {
            $query->whereHas('journalSlot', fn($q) =>
                $q->where('journal_master_id', $request->journal_master_id)
            );
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('judul_artikel', 'like', "%{$s}%")
                  ->orWhere('kode_submit', 'like', "%{$s}%")
                  ->orWhere('nama_penulis', 'like', "%{$s}%")
            );
        }

        $sortBy = $request->input('sort_by', 'date_asc');
        match ($sortBy) {
            'date_desc'  => $query->orderByDesc('tanggal_submit')->orderByDesc('id'),
            'title_asc'  => $query->orderBy('judul_artikel')->orderBy('id'),
            'title_desc' => $query->orderByDesc('judul_artikel')->orderBy('id'),
            default      => $query->orderBy('tanggal_submit')->orderBy('id'),
        };

        $submissions   = $query->paginate($request->input('per_page', 50))->withQueryString();
        $statusOptions = Submission::getStatusOptions();
        $journals      = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $pics          = Pic::where('is_active', true)->orderBy('name')->get();

        $pageTitle = match ($program) {
            'bkd'   => 'Monitoring Jurnal BKD',
            'jafa'  => 'Monitoring Jurnal JAFA',
            default => 'Monitoring Jurnal Normal',
        };

        return view('admin.pic-reviewer.monitoring', compact(
            'submissions', 'statusOptions', 'journals', 'pics', 'program', 'pageTitle'
        ));
    }
}
