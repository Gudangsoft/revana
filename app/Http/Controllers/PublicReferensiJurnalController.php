<?php

namespace App\Http\Controllers;

use App\Models\ReferensiJurnal;
use App\Models\Setting;
use App\Services\CitationGenerator;
use Illuminate\Http\Request;

class PublicReferensiJurnalController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferensiJurnal::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_jurnal',  'like', "%{$s}%")
                  ->orWhere('jenis_jurnal','like', "%{$s}%")
                  ->orWhere('bidang_ilmu', 'like', "%{$s}%")
                  ->orWhere('referensi',   'like', "%{$s}%")
                  ->orWhere('judul_artikel','like', "%{$s}%")
                  ->orWhere('penulis',     'like', "%{$s}%");
            });
        }

        if ($request->filled('jenis_jurnal')) $query->where('jenis_jurnal', $request->jenis_jurnal);
        if ($request->filled('bidang_ilmu'))  $query->where('bidang_ilmu',  $request->bidang_ilmu);
        if ($request->filled('tahun'))        $query->where('tahun',        $request->tahun);

        $referensiJurnals = $query->latest()->paginate($request->input('per_page', 15))->withQueryString();

        // Pastikan setiap record punya format sitasi — generate on-the-fly jika belum ada
        $referensiJurnals->getCollection()->transform(function ($item) {
            if (empty($item->format_sitasi)) {
                $formats = CitationGenerator::generate($item->toArray());
                if ($formats) {
                    $item->format_sitasi = $formats;
                    // Simpan ke DB agar tidak perlu generate lagi berikutnya
                    $item->saveQuietly();
                }
            }
            return $item;
        });

        $jenisOptions  = ReferensiJurnal::select('jenis_jurnal')->distinct()->orderBy('jenis_jurnal')->pluck('jenis_jurnal');
        $bidangOptions = ReferensiJurnal::select('bidang_ilmu')->distinct()->orderBy('bidang_ilmu')->pluck('bidang_ilmu');
        $tahunOptions  = ReferensiJurnal::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $totalCount    = ReferensiJurnal::count();

        $settings = [
            'app_name'  => Setting::get('app_name',  env('APP_NAME', 'SIPERA')),
            'full_name' => Setting::get('full_name',  ''),
            'tagline'   => Setting::get('tagline',    ''),
            'logo'      => Setting::get('logo',       ''),
            'favicon'   => Setting::get('favicon',    ''),
        ];

        return view('public.referensi-jurnal', compact(
            'referensiJurnals', 'jenisOptions', 'bidangOptions', 'tahunOptions',
            'totalCount', 'settings'
        ));
    }
}
