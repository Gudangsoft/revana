<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use App\Models\Submission;
use Illuminate\Http\Request;

class InvoiceMasterController extends Controller
{
    // ── Index: daftar jurnal (pengaturan rekening bank) + cari submission untuk buka invoice ──
    public function index(Request $request)
    {
        $journals = JournalMaster::where('is_active', true)
            ->orderBy('nama_jurnal')->get();

        $search = trim((string) $request->query('search', ''));

        $submissions = Submission::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('nama_penulis', 'like', "%{$search}%")
                       ->orWhere('kode_submit', 'like', "%{$search}%")
                       ->orWhere('judul_artikel', 'like', "%{$search}%");
                });
            })
            ->with('journalSlot.journalMaster')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.invoice-master.index', compact('journals', 'submissions', 'search'));
    }

    // ── Edit: form pengaturan rekening bank per jurnal ───────────────────
    public function edit(JournalMaster $journalMaster)
    {
        return view('admin.invoice-master.edit', ['journal' => $journalMaster]);
    }

    // ── Update: simpan info rekening bank ────────────────────────────────
    public function update(Request $request, JournalMaster $journalMaster)
    {
        $validated = $request->validate([
            'bank_name'            => 'nullable|string|max:255',
            'bank_account_number'  => 'nullable|string|max:100',
            'bank_account_holder'  => 'nullable|string|max:255',
        ]);

        $journalMaster->update($validated);

        return redirect()->route('admin.invoice-master.index')
            ->with('success', 'Info rekening untuk "' . $journalMaster->nama_jurnal . '" berhasil disimpan.');
    }
}
