<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return view('admin.search.results', ['results' => collect(), 'q' => $q, 'tooShort' => true]);
        }

        $results = Submission::with(['journalSlot.journalMaster', 'marketing', 'petugasSubmit'])
            ->where(function ($query) use ($q) {
                $query->where('nama_penulis', 'like', "%{$q}%")
                    ->orWhere('id_artikel', 'like', "%{$q}%")
                    ->orWhere('judul_artikel', 'like', "%{$q}%")
                    ->orWhere('kode_submit', 'like', "%{$q}%")
                    ->orWhere('no_hp_penulis', 'like', "%{$q}%")
                    ->orWhereHas('journalSlot.journalMaster', function ($jq) use ($q) {
                        $jq->where('nama_jurnal', 'like', "%{$q}%");
                    });
            })
            ->latest()
            ->take(50)
            ->get();

        return view('admin.search.results', compact('results', 'q') + ['tooShort' => false]);
    }
}
