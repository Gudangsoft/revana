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
        $inProcessStatuses = ['SUBMITTED', 'PUBLISHED', 'REJECTED'];

        $query = JournalMaster::where('is_active', true)
            ->with(['slots' => function ($q) use ($inProcessStatuses) {
                $q->withCount([
                    'submissions as total_artikel',
                    'submissions as submitted'  => fn ($q) => $q->where('status', 'SUBMITTED'),
                    'submissions as published'  => fn ($q) => $q->where('status', 'PUBLISHED'),
                    'submissions as rejected'   => fn ($q) => $q->where('status', 'REJECTED'),
                    'submissions as in_process' => fn ($q) => $q->whereNotIn('status', $inProcessStatuses),
                ]);
            }])
            ->orderBy('nama_jurnal');

        if ($request->filled('journal_id')) {
            $query->where('id', $request->journal_id);
        }

        $journals = $query->get();

        $reportData = [];
        $grandTotal = [
            'total_slot'    => 0,
            'total_artikel' => 0,
            'submitted'     => 0,
            'in_process'    => 0,
            'published'     => 0,
            'rejected'      => 0,
        ];

        foreach ($journals as $journal) {
            $slots = $journal->slots;

            $data = [
                'journal'       => $journal,
                'total_slot'    => $slots->sum('jumlah_slot'),
                'total_artikel' => $slots->sum('total_artikel'),
                'submitted'     => $slots->sum('submitted'),
                'in_process'    => $slots->sum('in_process'),
                'published'     => $slots->sum('published'),
                'rejected'      => $slots->sum('rejected'),
                'slots'         => $slots->map(fn ($slot) => [
                    'slot'          => $slot,
                    'total_artikel' => $slot->total_artikel,
                    'submitted'     => $slot->submitted,
                    'in_process'    => $slot->in_process,
                    'published'     => $slot->published,
                    'rejected'      => $slot->rejected,
                ])->all(),
            ];

            $grandTotal['total_slot']    += $data['total_slot'];
            $grandTotal['total_artikel'] += $data['total_artikel'];
            $grandTotal['submitted']     += $data['submitted'];
            $grandTotal['in_process']    += $data['in_process'];
            $grandTotal['published']     += $data['published'];
            $grandTotal['rejected']      += $data['rejected'];

            if ($data['total_artikel'] > 0 || $request->filled('show_empty')) {
                $reportData[] = $data;
            }
        }

        $allJournals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get(['id', 'nama_jurnal']);
        $generatedAt = now()->format('d M Y H:i');

        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('reports.journal-article-pdf', compact('reportData', 'grandTotal', 'generatedAt'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('Laporan_Artikel_Per_Jurnal_' . now()->format('Y-m-d') . '.pdf');
        }

        $layout = 'layouts.app';
        if (auth()->guard('pic')->check()) {
            $layout = 'pic.layouts.app';
        } elseif (auth()->guard('marketing')->check()) {
            $layout = 'marketing.layouts.app';
        }

        return view('reports.journal-article', compact('reportData', 'grandTotal', 'allJournals', 'generatedAt', 'layout'));
    }
}
