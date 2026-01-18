<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use App\Models\Accreditation;
use Illuminate\Http\Request;

class JournalMasterController extends Controller
{
    public function index()
    {
        $journals = JournalMaster::with(['creator', 'slots'])
            ->latest()
            ->paginate(20);
            
        return view('admin.journal-masters.index', compact('journals'));
    }

    public function create()
    {
        $accreditations = Accreditation::where('is_active', true)->orderBy('points', 'desc')->get();
        return view('admin.journal-masters.create', compact('accreditations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_jurnal' => 'nullable|string|max:50|unique:journal_masters',
            'nama_jurnal' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'link_jurnal' => 'required|url',
            'accreditation' => 'nullable|string|max:50',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = true;

        JournalMaster::create($validated);

        return redirect()->route('admin.journal-masters.index')
            ->with('success', 'Data Jurnal berhasil ditambahkan');
    }

    public function show(JournalMaster $journalMaster)
    {
        $journalMaster->load(['creator', 'slots.submissions']);
        return view('admin.journal-masters.show', compact('journalMaster'));
    }

    public function edit(JournalMaster $journalMaster)
    {
        $accreditations = Accreditation::where('is_active', true)->orderBy('points', 'desc')->get();
        return view('admin.journal-masters.edit', compact('journalMaster', 'accreditations'));
    }

    public function update(Request $request, JournalMaster $journalMaster)
    {
        $validated = $request->validate([
            'kode_jurnal' => 'required|string|max:50|unique:journal_masters,kode_jurnal,' . $journalMaster->id,
            'nama_jurnal' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'link_jurnal' => 'required|url',
            'accreditation' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $journalMaster->update($validated);

        return redirect()->route('admin.journal-masters.index')
            ->with('success', 'Data Jurnal berhasil diperbarui');
    }

    public function destroy(JournalMaster $journalMaster)
    {
        // Check if journal has slots
        if ($journalMaster->slots()->count() > 0) {
            return back()->with('error', 'Jurnal tidak dapat dihapus karena masih memiliki slot');
        }

        $journalMaster->delete();

        return redirect()->route('admin.journal-masters.index')
            ->with('success', 'Data Jurnal berhasil dihapus');
    }

    public function toggleActive(JournalMaster $journalMaster)
    {
        $journalMaster->update(['is_active' => !$journalMaster->is_active]);
        
        $status = $journalMaster->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Jurnal berhasil {$status}");
    }
}
