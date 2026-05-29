<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ReferensiJurnalImport;
use App\Models\ReferensiJurnal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReferensiJurnalController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferensiJurnal::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_jurnal', 'like', "%{$search}%")
                  ->orWhere('jenis_jurnal', 'like', "%{$search}%")
                  ->orWhere('bidang_ilmu', 'like', "%{$search}%")
                  ->orWhere('referensi', 'like', "%{$search}%")
                  ->orWhere('kutipan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jenis_jurnal')) {
            $query->where('jenis_jurnal', $request->jenis_jurnal);
        }

        if ($request->filled('bidang_ilmu')) {
            $query->where('bidang_ilmu', $request->bidang_ilmu);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $referensiJurnals = $query->latest()->paginate($request->input('per_page', 20))->withQueryString();

        // Data untuk stat cards & filter dropdowns
        $totalCount         = ReferensiJurnal::count();
        $nasionalCount      = ReferensiJurnal::where('jenis_jurnal', 'like', '%Nasional%')->count();
        $internasionalCount = ReferensiJurnal::where('jenis_jurnal', 'like', '%Internasional%')->count();
        $jenisOptions       = ReferensiJurnal::select('jenis_jurnal')->distinct()->orderBy('jenis_jurnal')->pluck('jenis_jurnal');
        $bidangOptions      = ReferensiJurnal::select('bidang_ilmu')->distinct()->orderBy('bidang_ilmu')->pluck('bidang_ilmu');
        $tahunOptions       = ReferensiJurnal::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('admin.referensi-jurnals.index', compact(
            'referensiJurnals',
            'totalCount', 'nasionalCount', 'internasionalCount',
            'jenisOptions', 'bidangOptions', 'tahunOptions'
        ));
    }

    public function create()
    {
        $jenisOptions  = ReferensiJurnal::select('jenis_jurnal')->distinct()->orderBy('jenis_jurnal')->pluck('jenis_jurnal');
        $bidangOptions = ReferensiJurnal::select('bidang_ilmu')->distinct()->orderBy('bidang_ilmu')->pluck('bidang_ilmu');
        return view('admin.referensi-jurnals.create', compact('jenisOptions', 'bidangOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jurnal'  => 'required|string|max:255',
            'jenis_jurnal' => 'required|string|max:100',
            'bidang_ilmu'  => 'required|string|max:100',
            'tahun'        => 'required|integer|min:1900|max:2100',
            'referensi'    => 'required|string',
            'kutipan'      => 'nullable|string',
        ]);

        $validated['format_sitasi'] = $this->buildFormatSitasi($request);

        ReferensiJurnal::create($validated);

        return redirect()->route('admin.referensi-jurnals.index')
            ->with('success', 'Referensi Jurnal berhasil ditambahkan');
    }

    public function edit(ReferensiJurnal $referensiJurnal)
    {
        $jenisOptions  = ReferensiJurnal::select('jenis_jurnal')->distinct()->orderBy('jenis_jurnal')->pluck('jenis_jurnal');
        $bidangOptions = ReferensiJurnal::select('bidang_ilmu')->distinct()->orderBy('bidang_ilmu')->pluck('bidang_ilmu');
        return view('admin.referensi-jurnals.edit', compact('referensiJurnal', 'jenisOptions', 'bidangOptions'));
    }

    public function update(Request $request, ReferensiJurnal $referensiJurnal)
    {
        $validated = $request->validate([
            'nama_jurnal'  => 'required|string|max:255',
            'jenis_jurnal' => 'required|string|max:100',
            'bidang_ilmu'  => 'required|string|max:100',
            'tahun'        => 'required|integer|min:1900|max:2100',
            'referensi'    => 'required|string',
            'kutipan'      => 'nullable|string',
        ]);

        $validated['format_sitasi'] = $this->buildFormatSitasi($request);

        $referensiJurnal->update($validated);

        return redirect()->route('admin.referensi-jurnals.index')
            ->with('success', 'Referensi Jurnal berhasil diupdate');
    }

    private function buildFormatSitasi(Request $request): ?string
    {
        $formats = [];
        foreach (array_keys(\App\Models\ReferensiJurnal::STYLE_LABELS) as $style) {
            $key = 'sitasi_' . strtolower($style);
            $val = trim($request->input($key, ''));
            if ($val !== '') {
                $formats[$style] = $val;
            }
        }
        return $formats ? json_encode($formats, JSON_UNESCAPED_UNICODE) : null;
    }

    public function destroy(ReferensiJurnal $referensiJurnal)
    {
        $referensiJurnal->delete();

        return redirect()->route('admin.referensi-jurnals.index')
            ->with('success', 'Referensi Jurnal berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'File Excel wajib diunggah',
            'file.mimes'    => 'File harus berformat .xlsx, .xls, atau .csv',
            'file.max'      => 'Ukuran file maksimal 5MB',
        ]);

        try {
            $import = new ReferensiJurnalImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated  = $import->getUpdatedCount();

            if ($imported === 0 && $updated === 0) {
                $msg = 'Tidak ada data yang diimport atau diperbarui.';
            } else {
                $msg = 'Import berhasil! ';
                if ($imported > 0) $msg .= "{$imported} data baru ditambahkan. ";
                if ($updated  > 0) $msg .= "{$updated} data diperbarui.";
            }

            return redirect()->route('admin.referensi-jurnals.index')->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->route('admin.referensi-jurnals.index')
                ->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new class implements
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithStyles
        {
            public function array(): array
            {
                return [
                    [
                        'Konstruksi: Publikasi Ilmu Teknik',
                        'Jurnal Nasional',
                        'Teknik',
                        2026,
                        'Rahmadani, P. A., Tohar, I., & Hakim, R. (2026). Identifikasi Permasalahan Arsitektur Perpustakaan. Konstruksi: Publikasi Ilmu Teknik, 4(2), 01–10. https://doi.org/10.61132/konstruksi.v4i2.1349',
                        '(Rahmadani et al., 2026)',
                    ],
                    [
                        'Konstruksi: Publikasi Ilmu Teknik',
                        'Jurnal Nasional',
                        'Teknik',
                        2026,
                        'Mardian, N. A. P., Mufidah, M., & Hakim, R. (2026). Analisis Komparasi Panti Wreda. Konstruksi: Publikasi Ilmu Teknik, 4(2), 11–20. https://doi.org/10.61132/konstruksi.v4i2.1350',
                        '(Mardian et al., 2026)',
                    ],
                    [
                        'Jurnal Internasional Teknologi',
                        'Jurnal Internasional',
                        'Kecerdasan Buatan',
                        2024,
                        'Author, B. (2024). Deep Learning for Image Recognition. Int. J. Tech, 5(1), 20–35. https://doi.org/xxx',
                        'B. Author, "Deep Learning for Image Recognition," Int. J. Tech, vol. 5, no. 1, pp. 20–35, 2024.',
                    ],
                ];
            }

            public function headings(): array
            {
                return ['nama_jurnal', 'jenis_jurnal', 'bidang_ilmu', 'tahun', 'referensi', 'kutipan'];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [1 => ['font' => ['bold' => true]]];
            }
        }, 'template_referensi_jurnal.xlsx');
    }
}
