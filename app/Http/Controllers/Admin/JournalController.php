<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index()
    {
        $journals = Journal::with('creator')->latest()->paginate(20);
        return view('admin.journals.index', compact('journals'));
    }

    public function create()
    {
        return view('admin.journals.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'required|url',
            'accreditation' => 'required|in:SINTA 1,SINTA 2,SINTA 3,SINTA 4,SINTA 5,SINTA 6',
            'publisher' => 'nullable|string|max:255',
            'marketing' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();

        Journal::create($validated);

        return redirect()->route('admin.journals.index')
            ->with('success', 'Jurnal berhasil ditambahkan');
    }

    public function edit(Journal $journal)
    {
        return view('admin.journals.edit', compact('journal'));
    }

    public function update(Request $request, Journal $journal)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'required|url',
            'accreditation' => 'required|in:SINTA 1,SINTA 2,SINTA 3,SINTA 4,SINTA 5,SINTA 6',
            'publisher' => 'nullable|string|max:255',
            'marketing' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
        ]);

        $journal->update($validated);

        return redirect()->route('admin.journals.index')
            ->with('success', 'Jurnal berhasil diupdate');
    }

    public function destroy(Journal $journal)
    {
        $journal->delete();

        return redirect()->route('admin.journals.index')
            ->with('success', 'Jurnal berhasil dihapus');
    }
}
