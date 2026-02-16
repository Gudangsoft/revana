<?php

namespace App\Http\Controllers;

use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Submission;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Laporan per Jurnal - berapa artikel masuk per jurnal beserta status
     */
    public function journalArticleReport(Request $request)
    {
        $query = JournalMaster::where('is_active', true)
            ->with(['slots.submissions'])
            ->orderBy('nama_jurnal');

        if ($request->filled('journal_id')) {
            $query->where('id', $request->journal_id);
        }

        $journals = $query->get();

        // Build report data
        $reportData = [];
        $grandTotal = [
            'total_slot' => 0,
            'total_artikel' => 0,
            'submitted' => 0,
            'in_process' => 0,
            'published' => 0,
            'rejected' => 0,
        ];

        foreach ($journals as $journal) {
            $submissions = Submission::whereHas('journalSlot', function ($q) use ($journal) {
                $q->where('journal_master_id', $journal->id);
            })->get();

            $data = [
                'journal' => $journal,
                'total_slot' => $journal->slots->sum('jumlah_slot'),
                'total_artikel' => $submissions->count(),
                'submitted' => $submissions->where('status', 'SUBMITTED')->count(),
                'in_process' => $submissions->whereNotIn('status', ['SUBMITTED', 'PUBLISHED', 'REJECTED'])->count(),
                'published' => $submissions->where('status', 'PUBLISHED')->count(),
                'rejected' => $submissions->where('status', 'REJECTED')->count(),
                'slots' => [],
            ];

            // Detail per slot
            foreach ($journal->slots as $slot) {
                $slotSubmissions = $submissions->where('journal_slot_id', $slot->id);
                $data['slots'][] = [
                    'slot' => $slot,
                    'total_artikel' => $slotSubmissions->count(),
                    'submitted' => $slotSubmissions->where('status', 'SUBMITTED')->count(),
                    'in_process' => $slotSubmissions->whereNotIn('status', ['SUBMITTED', 'PUBLISHED', 'REJECTED'])->count(),
                    'published' => $slotSubmissions->where('status', 'PUBLISHED')->count(),
                    'rejected' => $slotSubmissions->where('status', 'REJECTED')->count(),
                ];
            }

            $grandTotal['total_slot'] += $data['total_slot'];
            $grandTotal['total_artikel'] += $data['total_artikel'];
            $grandTotal['submitted'] += $data['submitted'];
            $grandTotal['in_process'] += $data['in_process'];
            $grandTotal['published'] += $data['published'];
            $grandTotal['rejected'] += $data['rejected'];

            if ($data['total_artikel'] > 0 || $request->filled('show_empty')) {
                $reportData[] = $data;
            }
        }

        $allJournals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $generatedAt = now()->format('d M Y H:i');

        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('reports.journal-article-pdf', compact('reportData', 'grandTotal', 'generatedAt'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('Laporan_Artikel_Per_Jurnal_' . now()->format('Y-m-d') . '.pdf');
        }

        // Determine layout based on guard
        $layout = 'layouts.app'; // default admin
        if (auth()->guard('pic')->check()) {
            $layout = 'pic.layouts.app';
        } elseif (auth()->guard('marketing')->check()) {
            $layout = 'marketing.layouts.app';
        }

        return view('reports.journal-article', compact('reportData', 'grandTotal', 'allJournals', 'generatedAt', 'layout'));
    }
}
