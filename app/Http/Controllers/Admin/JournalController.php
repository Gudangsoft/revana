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
        $marketings = \App\Models\Marketing::where('is_active', true)->orderBy('name')->get();
        $pics = \App\Models\Pic::where('is_active', true)->orderBy('name')->get();
        return view('admin.journals.create', compact('marketings', 'pics'));
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
            'author_name' => 'nullable|string|max:255',
            'turnitin_link' => 'nullable|url',
        ]);

        $validated['created_by'] = auth()->id();

        Journal::create($validated);

        return redirect()->route('admin.journals.index')
            ->with('success', 'Jurnal berhasil ditambahkan');
    }

    public function edit(Journal $journal)
    {
        $marketings = \App\Models\Marketing::where('is_active', true)->orderBy('name')->get();
        $pics = \App\Models\Pic::where('is_active', true)->orderBy('name')->get();
        return view('admin.journals.edit', compact('journal', 'marketings', 'pics'));
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
            'author_name' => 'nullable|string|max:255',
            'turnitin_link' => 'nullable|url',
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
