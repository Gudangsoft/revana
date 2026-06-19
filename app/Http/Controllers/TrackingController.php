<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    // Halaman utama portal penulis — GET ?kode_loa=xxx otomatis search
    public function index(Request $request)
    {
        if ($request->filled('kode_loa')) {
            return $this->search($request);
        }
        return view('public.author-portal');
    }

    // Redirect lama /cek-artikel → /tracking-loa
    public function authorPortal()
    {
        return redirect()->route('tracking.index');
    }

    public function authorPortalSearch(Request $request)
    {
        return redirect()->route('tracking.index');
    }

    // POST /tracking-loa/search — field: kode_loa (atau kode untuk compat)
    public function search(Request $request)
    {
        $field = $request->has('kode_loa') ? 'kode_loa' : 'kode';
        $request->validate([
            $field => 'required|string|max:80',
        ], [
            "$field.required" => 'Kode SIPERA wajib diisi.',
        ]);

        $kode = strtoupper(trim($request->input($field)));

        $submission = Submission::with(['journalSlot.journalMaster'])
            ->where('kode_submit', $kode)
            ->orWhere('kode_loa', $kode)
            ->first();

        if (!$submission) {
            return back()->withInput()
                ->withErrors([$field => 'Kode tidak ditemukan. Periksa kembali kode SIPERA Anda.']);
        }

        return view('public.author-portal', compact('submission'));
    }

    // Direct verification via URL (for QR Code) — keep for backwards compat
    public function verifyDirect($kodeLOA)
    {
        $kodeLOA = strtoupper(trim($kodeLOA));

        $submission = Submission::with(['journalSlot.journalMaster'])
            ->where('kode_loa', $kodeLOA)
            ->orWhere('kode_submit', $kodeLOA)
            ->first();

        if (!$submission) {
            return redirect()->route('tracking.index')->with('error', 'Kode LOA tidak ditemukan.');
        }

        return view('public.author-portal', compact('submission'));
    }
}
