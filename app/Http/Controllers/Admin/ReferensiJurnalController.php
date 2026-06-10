<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReferensiJurnalsExport;
use App\Http\Controllers\Controller;
use App\Imports\ReferensiJurnalImport;
use App\Models\ReferensiJurnal;
use App\Services\CitationGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            'nama_jurnal'   => 'required|string|max:255',
            'penulis'       => 'nullable|string',
            'judul_artikel' => 'nullable|string',
            'volume'        => 'nullable|string|max:20',
            'nomor'         => 'nullable|string|max:20',
            'halaman'       => 'nullable|string|max:40',
            'doi'           => 'nullable|string|max:255',
            'jenis_jurnal'  => 'required|string|max:100',
            'bidang_ilmu'   => 'required|string|max:100',
            'tahun'         => 'required|integer|min:1900|max:2100',
            'referensi'     => 'nullable|string',
            'kutipan'       => 'nullable|string',
        ]);

        $validated['format_sitasi'] = $this->buildFormatSitasi($validated);

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
            'nama_jurnal'   => 'required|string|max:255',
            'penulis'       => 'nullable|string',
            'judul_artikel' => 'nullable|string',
            'volume'        => 'nullable|string|max:20',
            'nomor'         => 'nullable|string|max:20',
            'halaman'       => 'nullable|string|max:40',
            'doi'           => 'nullable|string|max:255',
            'jenis_jurnal'  => 'required|string|max:100',
            'bidang_ilmu'   => 'required|string|max:100',
            'tahun'         => 'required|integer|min:1900|max:2100',
            'referensi'     => 'nullable|string',
            'kutipan'       => 'nullable|string',
        ]);

        $validated['format_sitasi'] = $this->buildFormatSitasi($validated);

        $referensiJurnal->update($validated);

        return redirect()->route('admin.referensi-jurnals.index')
            ->with('success', 'Referensi Jurnal berhasil diupdate');
    }

    public function fetchFromUrl(Request $request)
    {
        $request->validate(['url' => 'required|url']);
        $url = $request->input('url');

        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; CitationFetcher/1.0; +https://apji.org)'])
                ->get($url);

            if (!$response->successful()) {
                return response()->json(['error' => 'Halaman tidak dapat diakses (HTTP ' . $response->status() . ')'], 422);
            }

            $html = $response->body();
            $data = $this->parseArticleMetadata($html, $url);

            if (empty($data['judul_artikel']) && empty($data['penulis'])) {
                return response()->json(['error' => 'Metadata artikel tidak ditemukan di halaman ini. Pastikan URL mengarah ke halaman artikel jurnal.'], 422);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengakses URL: ' . $e->getMessage()], 422);
        }
    }

    private function parseArticleMetadata(string $html, string $sourceUrl): array
    {
        $data = [
            'judul_artikel' => '', 'penulis'     => '',
            'nama_jurnal'   => '', 'tahun'       => '',
            'volume'        => '', 'nomor'       => '',
            'halaman'       => '', 'doi'         => '',
            'jenis_jurnal'  => '', 'abstract'    => '',
        ];

        // ── 1. citation_* meta tags (Google Scholar / OJS standard) ──
        $metaAuthors = [];

        // Attribute order: name then content
        preg_match_all('/<meta[^>]+name=["\']citation_([a-z_]+)["\'][^>]+content=["\']([^"\']*)["\'][^>]*\/?>/i', $html, $m1);
        // Attribute order: content then name
        preg_match_all('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']citation_([a-z_]+)["\'][^>]*\/?>/i', $html, $m2);

        $metaTags = [];
        foreach (array_map(null, $m1[1], $m1[2]) as [$k, $v]) {
            if ($k === 'author') { $metaAuthors[] = trim($v); }
            else { $metaTags[$k] = trim($v); }
        }
        foreach (array_map(null, $m2[2], $m2[1]) as [$k, $v]) {
            if ($k === 'author') { $metaAuthors[] = trim($v); }
            elseif (!isset($metaTags[$k])) { $metaTags[$k] = trim($v); }
        }

        if (!empty($metaTags['title']))         $data['judul_artikel'] = $metaTags['title'];
        if (!empty($metaTags['journal_title'])) $data['nama_jurnal']   = $metaTags['journal_title'];
        if (!empty($metaTags['volume']))        $data['volume']        = $metaTags['volume'];
        if (!empty($metaTags['issue']))         $data['nomor']         = $metaTags['issue'];
        if (!empty($metaTags['doi']))           $data['doi']           = $metaTags['doi'];
        if (!empty($metaTags['abstract_html_url'])) {}

        // Year
        $rawDate = $metaTags['publication_date'] ?? $metaTags['date'] ?? $metaTags['year'] ?? '';
        if ($rawDate) { preg_match('/(\d{4})/', $rawDate, $ym); if ($ym) $data['tahun'] = $ym[1]; }

        // Pages
        $fp = $metaTags['firstpage'] ?? ''; $lp = $metaTags['lastpage'] ?? '';
        if ($fp && $lp) $data['halaman'] = "$fp–$lp";
        elseif ($fp)    $data['halaman'] = $fp;

        // Authors — keep original format, deduplicate
        $metaAuthors = array_values(array_unique(array_filter($metaAuthors)));
        if ($metaAuthors) {
            if (count($metaAuthors) === 1) {
                $data['penulis'] = $metaAuthors[0];
            } else {
                $last = array_pop($metaAuthors);
                $data['penulis'] = implode(', ', $metaAuthors) . ', & ' . $last;
            }
        }

        // ── 2. JSON-LD (schema.org) ──
        if (empty($data['judul_artikel'])) {
            preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $jm);
            foreach ($jm[1] as $jsonStr) {
                $json = json_decode($jsonStr, true);
                if (!$json) continue;
                $items = isset($json['@graph']) ? $json['@graph'] : [$json];
                foreach ($items as $item) {
                    $type = $item['@type'] ?? '';
                    if (!in_array($type, ['ScholarlyArticle','Article','NewsArticle'])) continue;
                    if (!empty($item['headline']) && empty($data['judul_artikel'])) $data['judul_artikel'] = $item['headline'];
                    if (!empty($item['name'])     && empty($data['judul_artikel'])) $data['judul_artikel'] = $item['name'];
                    if (!empty($item['datePublished']) && empty($data['tahun'])) {
                        preg_match('/(\d{4})/', $item['datePublished'], $dy);
                        if ($dy) $data['tahun'] = $dy[1];
                    }
                    if (!empty($item['author']) && empty($data['penulis'])) {
                        $authors = is_array($item['author']) ? $item['author'] : [$item['author']];
                        $names = array_map(fn($a) => $a['name'] ?? '', $authors);
                        $names = array_filter($names);
                        $last  = array_pop($names);
                        $data['penulis'] = $names ? implode(', ', $names) . ', & ' . $last : $last;
                    }
                    if (!empty($item['isPartOf']['volumeNumber']) && empty($data['volume'])) $data['volume'] = $item['isPartOf']['volumeNumber'];
                    if (!empty($item['isPartOf']['issueNumber'])  && empty($data['nomor']))  $data['nomor']  = $item['isPartOf']['issueNumber'];
                }
            }
        }

        // ── 3. Dublin Core / Open Graph fallback ──
        if (empty($data['judul_artikel'])) {
            preg_match('/<meta[^>]+(?:name|property)=["\'](?:DC\.title|og:title)["\'][^>]+content=["\']([^"\']+)["\'][^>]*\/?>/i', $html, $og);
            if ($og) $data['judul_artikel'] = html_entity_decode(trim($og[1]), ENT_QUOTES);
        }
        if (empty($data['nama_jurnal'])) {
            preg_match('/<meta[^>]+(?:name|property)=["\'](?:og:site_name|DC\.source)["\'][^>]+content=["\']([^"\']+)["\'][^>]*\/?>/i', $html, $site);
            if ($site) $data['nama_jurnal'] = html_entity_decode(trim($site[1]), ENT_QUOTES);
        }

        // Clean HTML entities
        foreach ($data as &$v) { $v = html_entity_decode(trim($v), ENT_QUOTES | ENT_HTML5, 'UTF-8'); }
        unset($v);

        // Auto-detect jenis jurnal
        $namaL = strtolower($data['nama_jurnal']);
        if (str_contains($namaL, 'international') || str_contains($namaL, 'global') || str_contains($namaL, 'world')) {
            $data['jenis_jurnal'] = 'Jurnal Internasional';
        } elseif ($data['nama_jurnal']) {
            $data['jenis_jurnal'] = 'Jurnal Nasional';
        }

        return $data;
    }

    private function buildFormatSitasi(array $data): ?string
    {
        // Sertakan nama_jurnal dari validated data
        $formats = CitationGenerator::generate($data);
        return $formats ? json_encode($formats, JSON_UNESCAPED_UNICODE) : null;
    }

    public function destroy(ReferensiJurnal $referensiJurnal)
    {
        $referensiJurnal->delete();

        return redirect()->route('admin.referensi-jurnals.index')
            ->with('success', 'Referensi Jurnal berhasil dihapus');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'jenis_jurnal', 'bidang_ilmu', 'tahun']);
        return Excel::download(new ReferensiJurnalsExport($filters), 'referensi-jurnal.xlsx');
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
                        'Rahmadani, P. A., Tohar, I., & Hakim, R.',
                        'Identifikasi Permasalahan Arsitektur Perpustakaan Umum Daerah Kabupaten',
                        '4', '2', '01–10',
                        '10.61132/konstruksi.v4i2.1349',
                        'Rahmadani, P. A., Tohar, I., & Hakim, R. (2026). Identifikasi Permasalahan Arsitektur. Konstruksi, 4(2), 01–10.',
                        '',
                    ],
                    [
                        'Konstruksi: Publikasi Ilmu Teknik',
                        'Jurnal Nasional',
                        'Teknik',
                        2026,
                        'Mardian, N. A. P., Mufidah, M., & Hakim, R.',
                        'Analisis Komparasi Panti Wreda Berdasarkan Pelayanan Holistik',
                        '4', '2', '11–20',
                        '10.61132/konstruksi.v4i2.1350',
                        'Mardian, N. A. P., Mufidah, M., & Hakim, R. (2026). Analisis Komparasi Panti Wreda. Konstruksi, 4(2), 11–20.',
                        '',
                    ],
                ];
            }

            public function headings(): array
            {
                return [
                    'nama_jurnal', 'jenis_jurnal', 'bidang_ilmu', 'tahun',
                    'penulis', 'judul_artikel',
                    'volume', 'nomor', 'halaman', 'doi',
                    'referensi', 'kutipan',
                ];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [1 => ['font' => ['bold' => true]]];
            }
        }, 'template_referensi_jurnal.xlsx');
    }
}
