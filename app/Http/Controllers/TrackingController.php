<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Show tracking page
     */
    public function index()
    {
        return view('public.tracking');
    }

    /**
     * Direct verification via URL (for QR Code)
     */
    public function verifyDirect($kodeLOA)
    {
        $kodeLOA = strtoupper(trim($kodeLOA));

        $submission = Submission::with([
            'journalSlot.journalMaster', 
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction'
        ])
            ->where('kode_loa', $kodeLOA)
            ->orWhere('kode_submit', $kodeLOA)
            ->first();

        if (!$submission) {
            $route = str_contains(request()->route()->getName(), 'verify') ? 'verify.index' : 'tracking.index';
            return redirect()->route($route)->with('error', 'Kode LOA tidak ditemukan.');
        }

        return view('public.tracking-result', compact('submission'));
    }

    /**
     * Search by LOA code
     */
    public function search(Request $request)
    {
        $request->validate([
            'kode_loa' => 'required|string',
        ]);

        $kodeLOA = strtoupper(trim($request->kode_loa));

        $submission = Submission::with([
            'journalSlot.journalMaster', 
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction'
        ])
            ->where('kode_loa', $kodeLOA)
            ->orWhere('kode_submit', $kodeLOA)
            ->first();

        if (!$submission) {
            return back()->with('error', 'Kode LOA tidak ditemukan. Pastikan Anda memasukkan kode yang benar.');
        }

        return view('public.tracking-result', compact('submission'));
    }
}
