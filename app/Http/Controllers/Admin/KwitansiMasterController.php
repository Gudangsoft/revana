<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KwitansiMasterController extends Controller
{
    // ── Index: daftar jurnal (pengaturan Bendahara) + cari submission untuk buka kwitansi ──
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

        return view('admin.kwitansi-master.index', compact('journals', 'submissions', 'search'));
    }

    // ── Edit: form pengaturan Bendahara khusus kwitansi (terpisah dari editor LOA) ──
    public function edit(JournalMaster $journalMaster)
    {
        return view('admin.kwitansi-master.edit', ['journal' => $journalMaster]);
    }

    // ── Update: simpan nama & tanda tangan Bendahara ────────────────────
    public function update(Request $request, JournalMaster $journalMaster)
    {
        $request->validate([
            'bendahara_name'      => 'nullable|string|max:255',
            'bendahara_signature' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['bendahara_name']);

        if ($request->hasFile('bendahara_signature')) {
            if ($journalMaster->bendahara_signature_path) {
                Storage::disk('public')->delete($journalMaster->bendahara_signature_path);
            }
            $data['bendahara_signature_path'] = $request->file('bendahara_signature')->store('journals/signatures', 'public');
        }
        if ($request->boolean('remove_signature') && $journalMaster->bendahara_signature_path) {
            Storage::disk('public')->delete($journalMaster->bendahara_signature_path);
            $data['bendahara_signature_path'] = null;
        }

        $journalMaster->update($data);

        return redirect()->route('admin.kwitansi-master.index')
            ->with('success', 'Pengaturan Bendahara untuk "' . $journalMaster->nama_jurnal . '" berhasil disimpan.');
    }
}
