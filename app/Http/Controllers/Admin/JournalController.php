<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function monitoringSlots()
    {
        $journals = Journal::with(['assignments', 'creator'])
            ->latest()
            ->get();

        // Calculate statistics
        $totalAssignments = \App\Models\ReviewAssignment::count();
        $stats = [
            'total_journals' => $journals->count(),
            'total_slots' => $journals->sum('slot') ?? 0,
            'slots_used' => $totalAssignments,
            'slots_available' => ($journals->sum('slot') ?? 0) - $totalAssignments,
        ];

        // Calculate per journal
        $journalStats = $journals->map(function($journal) {
            $totalSlots = $journal->slot ?? 0;
            $usedSlots = $journal->assignments->count();
            $availableSlots = $totalSlots - $usedSlots;
            $percentage = $totalSlots > 0 ? ($usedSlots / $totalSlots) * 100 : 0;
            
            return [
                'journal' => $journal,
                'total_slots' => $totalSlots,
                'used_slots' => $usedSlots,
                'available_slots' => $availableSlots,
                'percentage' => round($percentage, 1),
                'status' => $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success')
            ];
        })->sortByDesc('percentage');

        return view('admin.journals.monitoring', compact('stats', 'journalStats'));
    }

    public function index()
    {
        $journals = Journal::with('creator')->latest()->paginate(20);
        return view('admin.journals.index', compact('journals'));
    }

    public function create()
    {
        $accreditations = \App\Models\Accreditation::where('is_active', true)->orderBy('points', 'desc')->get();
        $marketings = \App\Models\Marketing::where('is_active', true)->orderBy('name')->get();
        $pics = \App\Models\Pic::where('is_active', true)->orderBy('name')->get();
        return view('admin.journals.create', compact('accreditations', 'marketings', 'pics'));
    }

    public function store(Request $request)
    {
        $accreditations = \App\Models\Accreditation::where('is_active', true)->pluck('name')->toArray();
        
        $validated = $request->validate([
            'slot' => 'required|integer|min:1',
            'volume' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'link' => 'required|url',
            'accreditation' => ['required', 'string', \Illuminate\Validation\Rule::in($accreditations)],
            'publisher' => 'nullable|string|max:255',
            'marketing' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'author_username' => 'nullable|string|max:255',
            'author_password' => 'nullable|string|max:255',
            'turnitin_link' => 'nullable|url',
            'loa_link' => 'nullable|url',
        ]);

        $validated['created_by'] = auth()->id();

        Journal::create($validated);

        return redirect()->route('admin.journals.index')
            ->with('success', 'Jurnal berhasil ditambahkan');
    }

    public function edit(Journal $journal)
    {
        $accreditations = \App\Models\Accreditation::where('is_active', true)->orderBy('points', 'desc')->get();
        $marketings = \App\Models\Marketing::where('is_active', true)->orderBy('name')->get();
        $pics = \App\Models\Pic::where('is_active', true)->orderBy('name')->get();
        return view('admin.journals.edit', compact('journal', 'accreditations', 'marketings', 'pics'));
    }

    public function update(Request $request, Journal $journal)
    {
        $accreditations = \App\Models\Accreditation::where('is_active', true)->pluck('name')->toArray();
        
        $validated = $request->validate([
            'slot' => 'required|integer|min:1',
            'volume' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'link' => 'required|url',
            'accreditation' => ['required', 'string', \Illuminate\Validation\Rule::in($accreditations)],
            'publisher' => 'nullable|string|max:255',
            'marketing' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'author_username' => 'nullable|string|max:255',
            'author_password' => 'nullable|string|max:255',
            'turnitin_link' => 'nullable|url',
            'loa_link' => 'nullable|url',
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
