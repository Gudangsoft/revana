<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use App\Models\Submission;
use Illuminate\Http\Request;

class PicReviewerJournalController extends Controller
{
    public function submissions(Request $request, string $type = 'normal')
    {
        $query = Submission::with([
            'journalSlot.journalMaster',
            'petugasReviewer1',
            'petugasReviewer2',
        ]);

        match ($type) {
            'fasttrack' => $query->where('process_type', 'fasttrack'),
            'bkd'       => $query->where('program_type', 'bkd'),
            'jafa'      => $query->where('program_type', 'jafa'),
            default     => $query->whereNull('process_type')
                                 ->where(fn($q) => $q->whereNull('program_type')
                                                     ->orWhere('program_type', '')),
        };

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('judul_artikel', 'like', "%{$s}%")
                  ->orWhere('kode_submit', 'like', "%{$s}%")
                  ->orWhere('nama_penulis', 'like', "%{$s}%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('journal_search')) {
            $js = $request->journal_search;
            $query->whereHas('journalSlot.journalMaster', fn($q) =>
                $q->where('nama_jurnal', 'like', "%{$js}%")
            );
        }

        $submissions   = $query->latest('tanggal_submit')->paginate(30)->appends($request->query());
        $statusOptions = Submission::getStatusOptions();
        $journals      = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();

        $titles = [
            'normal'    => 'Jurnal Normal',
            'fasttrack' => 'Jurnal Fasttrack',
            'bkd'       => 'Jurnal BKD',
            'jafa'      => 'Jurnal JAFA',
        ];

        return view('admin.pic-reviewer.submissions', compact(
            'submissions', 'statusOptions', 'journals', 'type'
        ))->with('pageTitle', $titles[$type] ?? 'Jurnal');
    }
}
