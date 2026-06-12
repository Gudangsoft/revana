<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\ScreeningForm;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ScreeningFormController extends Controller
{
    /** Definisi semua item checklist per seksi */
    public static function checklistDefinition(): array
    {
        return [
            'A' => [
                'title' => 'Kesesuaian Scope Jurnal',
                'icon'  => 'bi-journal-bookmark',
                'items' => [
                    'a1' => 'Topik sesuai dengan fokus dan scope jurnal',
                    'a2' => 'Relevan dengan bidang keilmuan jurnal',
                    'a3' => 'Tidak keluar dari tema edisi (jika ada)',
                ],
            ],
            'B' => [
                'title' => 'Kelengkapan Administratif',
                'icon'  => 'bi-clipboard-check',
                'items' => [
                    'b1' => 'Artikel menggunakan template jurnal',
                    'b2' => 'File lengkap (manuscript, gambar, tabel)',
                    'b3' => 'Metadata lengkap di OJS (judul, abstrak, author, afiliasi)',
                    'b4' => 'Penulis minimal 1 dan jelas afiliasinya',
                    'b5' => 'Corresponding author jelas',
                ],
            ],
            'C' => [
                'title' => 'Struktur Artikel',
                'icon'  => 'bi-layout-text-window',
                'items' => [
                    'c1' => 'Judul jelas dan informatif',
                    'c2' => 'Abstrak (Indonesia/Inggris) tersedia',
                    'c3' => 'Kata kunci (3–5 kata)',
                    'c4' => 'Pendahuluan',
                    'c5' => 'Metodologi',
                    'c6' => 'Hasil dan Pembahasan',
                    'c7' => 'Kesimpulan',
                    'c8' => 'Daftar pustaka',
                ],
            ],
            'D' => [
                'title' => 'Kualitas Penulisan',
                'icon'  => 'bi-pen',
                'items' => [
                    'd1' => 'Bahasa baik dan mudah dipahami',
                    'd2' => 'Tidak banyak kesalahan typo',
                    'd3' => 'Konsisten format (heading, font, spasi)',
                ],
            ],
            'E' => [
                'title' => 'Originalitas (Plagiarism Check)',
                'icon'  => 'bi-shield-check',
                'items' => [
                    'e1' => 'Similarity < 25% (Turnitin/iThenticate)',
                    'e2' => 'Tidak terindikasi plagiarisme',
                    'e3' => 'Sitasi sesuai standar',
                ],
            ],
            'F' => [
                'title' => 'Kualitas Referensi',
                'icon'  => 'bi-book',
                'items' => [
                    'f1' => 'Menggunakan referensi terbaru (5–10 tahun terakhir)',
                    'f2' => 'Minimal 15–20 referensi (sesuai standar jurnal)',
                    'f3' => 'Menggunakan jurnal ilmiah bereputasi',
                    'f4' => 'Format sitasi sesuai (APA / Vancouver / IEEE)',
                ],
            ],
            'G' => [
                'title' => 'Etika Publikasi',
                'icon'  => 'bi-check2-all',
                'items' => [
                    'g1' => 'Tidak mengandung unsur SARA / plagiarisme',
                    'g2' => 'Tidak sedang disubmit di jurnal lain',
                    'g3' => 'Tidak melanggar etika penelitian',
                ],
            ],
        ];
    }

    /** Daftar 100 catatan pre-fill */
    public static function catatanPreset(): array
    {
        return [
            // Diterima (1-50)
            'diterima' => [
                1  => 'Artikel sesuai dengan scope jurnal dan layak direview.',
                2  => 'Struktur artikel sudah lengkap dan sistematis.',
                3  => 'Format sudah sesuai template jurnal.',
                4  => 'Abstrak jelas dan representatif.',
                5  => 'Metodologi penelitian disusun dengan baik.',
                6  => 'Referensi cukup dan relevan.',
                7  => 'Tingkat similarity masih dalam batas wajar.',
                8  => 'Penulisan sudah cukup rapi dan konsisten.',
                9  => 'Artikel memiliki kontribusi ilmiah yang jelas.',
                10 => 'Topik menarik dan relevan dengan isu terkini.',
                11 => 'Data penelitian cukup kuat dan valid.',
                12 => 'Hasil dan pembahasan tersusun baik.',
                13 => 'Tidak ditemukan indikasi plagiarisme.',
                14 => 'Sitasi sudah mengikuti standar.',
                15 => 'Bahasa cukup baik dan mudah dipahami.',
                16 => 'Artikel siap untuk proses review.',
                17 => 'Penulisan ilmiah sudah sesuai kaidah.',
                18 => 'Judul sudah mencerminkan isi artikel.',
                19 => 'Kata kunci relevan dan representatif.',
                20 => 'Penulis telah mengikuti pedoman jurnal.',
                21 => 'Grafik dan tabel mendukung analisis.',
                22 => 'Struktur IMRAD sudah terpenuhi.',
                23 => 'Konsistensi format sudah baik.',
                24 => 'Referensi terbaru sudah digunakan.',
                25 => 'Artikel layak masuk tahap review.',
                26 => 'Penyajian data cukup jelas.',
                27 => 'Analisis cukup mendalam.',
                28 => 'Penulis menunjukkan pemahaman yang baik.',
                29 => 'Artikel memiliki novelty yang cukup.',
                30 => 'Kesesuaian topik sangat baik.',
                31 => 'Penyusunan paragraf sudah sistematis.',
                32 => 'Abstrak bilingual sudah tersedia.',
                33 => 'Tidak ada kesalahan fatal pada struktur.',
                34 => 'Penulisan sesuai standar akademik.',
                35 => 'Layout artikel sudah rapi.',
                36 => 'Artikel memenuhi standar minimum jurnal.',
                37 => 'Format sitasi konsisten.',
                38 => 'Artikel siap masuk proses reviewer.',
                39 => 'Data mendukung kesimpulan.',
                40 => 'Kesimpulan jelas dan logis.',
                41 => 'Referensi berasal dari sumber terpercaya.',
                42 => 'Artikel menunjukkan kualitas akademik yang baik.',
                43 => 'Penelitian relevan dengan bidang jurnal.',
                44 => 'Penulis mengikuti template dengan baik.',
                45 => 'Struktur logis dan runtut.',
                46 => 'Artikel memenuhi syarat administratif.',
                47 => 'Tidak ditemukan pelanggaran etika.',
                48 => 'Artikel dapat diproses lebih lanjut.',
                49 => 'Penulisan sudah sesuai standar ilmiah.',
                50 => 'Artikel diterima untuk tahap review.',
            ],
            // Revisi (51-100)
            'revisi' => [
                51 => 'Format artikel belum sesuai template jurnal.',
                52 => 'Abstrak belum tersedia dalam bahasa Inggris.',
                53 => 'Kata kunci kurang relevan.',
                54 => 'Struktur artikel belum lengkap.',
                55 => 'Metodologi belum dijelaskan secara detail.',
                56 => 'Referensi masih kurang dan perlu ditambah.',
                57 => 'Tingkat similarity cukup tinggi, perlu revisi.',
                58 => 'Penulisan masih banyak typo.',
                59 => 'Konsistensi format belum rapi.',
                60 => 'Sitasi belum sesuai gaya jurnal.',
                61 => 'Judul terlalu panjang dan kurang fokus.',
                62 => 'Abstrak belum mencerminkan isi artikel.',
                63 => 'Pendahuluan kurang kuat secara teori.',
                64 => 'Pembahasan masih kurang mendalam.',
                65 => 'Kesimpulan terlalu umum.',
                66 => 'Referensi kurang up-to-date.',
                67 => 'Tabel/gambar belum diberi keterangan jelas.',
                68 => 'Penulisan bahasa perlu diperbaiki.',
                69 => 'Struktur IMRAD belum terpenuhi.',
                70 => 'Artikel belum mengikuti panduan penulis.',
                71 => 'Format daftar pustaka belum sesuai.',
                72 => 'Data penelitian belum dijelaskan dengan baik.',
                73 => 'Metode penelitian perlu diperjelas.',
                74 => 'Analisis masih kurang mendalam.',
                75 => 'Perlu penambahan referensi jurnal terbaru.',
                76 => 'Paragraf masih tidak sistematis.',
                77 => 'Perlu perbaikan pada bagian hasil.',
                78 => 'Penjelasan masih terlalu deskriptif.',
                79 => 'Tidak ada pembahasan yang kuat.',
                80 => 'Kesimpulan tidak didukung data.',
                81 => 'Penulisan angka dan simbol belum konsisten.',
                82 => 'Perlu perbaikan pada format heading.',
                83 => 'Layout artikel belum rapi.',
                84 => 'Referensi tidak konsisten.',
                85 => 'Abstrak terlalu singkat.',
                86 => 'Penulisan belum mengikuti kaidah ilmiah.',
                87 => 'Perlu revisi minor pada grammar.',
                88 => 'Penulisan istilah belum konsisten.',
                89 => 'Terdapat bagian yang tidak relevan.',
                90 => 'Artikel perlu disesuaikan dengan scope jurnal.',
                91 => 'Perlu perbaikan pada bagian pendahuluan.',
                92 => 'Metode tidak dijelaskan secara rinci.',
                93 => 'Hasil penelitian belum jelas.',
                94 => 'Perlu penambahan data pendukung.',
                95 => 'Referensi belum cukup kuat.',
                96 => 'Perlu perbaikan struktur artikel.',
                97 => 'Artikel perlu revisi sebelum review.',
                98 => 'Mohon mengikuti template jurnal secara penuh.',
                99 => 'Artikel belum siap untuk proses review.',
                100 => 'Disarankan revisi terlebih dahulu sebelum dilanjutkan.',
            ],
        ];
    }

    public function show(Request $request, Submission $submission)
    {
        $screening = ScreeningForm::where('submission_id', $submission->id)->first();
        if (!$screening) {
            return redirect()->route('admin.screenings.create', $submission);
        }
        $definition = self::checklistDefinition();
        $presets    = self::catatanPreset();
        return view('admin.screenings.show', compact('submission', 'screening', 'definition', 'presets'));
    }

    public function create(Submission $submission)
    {
        $existing = ScreeningForm::where('submission_id', $submission->id)->first();
        if ($existing) {
            return redirect()->route('admin.screenings.edit', [$submission, $existing]);
        }
        $definition = self::checklistDefinition();
        $presets    = self::catatanPreset();
        $screening    = null;
        $defaultEmail = $submission->email_penulis;
        return view('admin.screenings.form', compact('submission', 'screening', 'definition', 'presets', 'defaultEmail'));
    }

    public function store(Request $request, Submission $submission)
    {
        $request->validate([
            'keputusan'       => 'required|in:diterima,revisi,ditolak',
            'recipient_email' => 'nullable|email|max:255',
            'similarity_score'=> 'nullable|numeric|min:0|max:100',
            'catatan'         => 'nullable|string',
        ]);

        $checklist = $this->buildChecklist($request);

        $screening = ScreeningForm::create([
            'submission_id'   => $submission->id,
            'screened_by'     => Auth::id(),
            'checklist'       => $checklist,
            'similarity_score'=> $request->similarity_score,
            'keputusan'       => $request->keputusan,
            'catatan'         => $request->catatan,
            'recipient_email' => $request->recipient_email,
        ]);

        if ($request->has('send_email')) {
            $this->doSendEmail($screening);
        }

        return redirect()->route('admin.screenings.show', $submission)
            ->with('success', 'Screening berhasil disimpan.' . ($request->has('send_email') ? ' Email terkirim.' : ''));
    }

    public function edit(Submission $submission, ScreeningForm $screening)
    {
        $definition = self::checklistDefinition();
        $presets    = self::catatanPreset();
        return view('admin.screenings.form', compact('submission', 'screening', 'definition', 'presets'));
    }

    public function update(Request $request, Submission $submission, ScreeningForm $screening)
    {
        $request->validate([
            'keputusan'       => 'required|in:diterima,revisi,ditolak',
            'recipient_email' => 'nullable|email|max:255',
            'similarity_score'=> 'nullable|numeric|min:0|max:100',
            'catatan'         => 'nullable|string',
        ]);

        $checklist = $this->buildChecklist($request);

        $screening->update([
            'screened_by'     => Auth::id(),
            'checklist'       => $checklist,
            'similarity_score'=> $request->similarity_score,
            'keputusan'       => $request->keputusan,
            'catatan'         => $request->catatan,
            'recipient_email' => $request->recipient_email,
        ]);

        if ($request->has('send_email')) {
            $this->doSendEmail($screening);
        }

        return redirect()->route('admin.screenings.show', $submission)
            ->with('success', 'Screening berhasil diperbarui.' . ($request->has('send_email') ? ' Email terkirim.' : ''));
    }

    public function sendEmail(Submission $submission, ScreeningForm $screening)
    {
        $sent = $this->doSendEmail($screening);
        if ($sent) {
            return response()->json(['success' => true, 'message' => 'Email berhasil dikirim ke ' . $screening->recipient_email]);
        }
        return response()->json(['success' => false, 'message' => 'Gagal kirim email. Cek SMTP atau template.'], 422);
    }

    private function doSendEmail(ScreeningForm $screening): bool
    {
        if (!$screening->recipient_email) return false;

        $triggerKey = 'screening_' . $screening->keputusan;
        $tpl = EmailTemplate::findActive($triggerKey);
        if (!$tpl) return false;

        $submission = $screening->submission;
        try {
            $rendered = $tpl->render([
                'nama_artikel'    => $submission->judul_artikel ?? '-',
                'kode_submit'     => $submission->kode_submit ?? '-',
                'id_artikel'      => $submission->id_artikel ?? $submission->id,
                'nama_penulis'    => $submission->nama_penulis ?? '-',
                'email_penulis'   => $screening->recipient_email,
                'keputusan'       => ScreeningForm::keputusanLabel($screening->keputusan),
                'similarity'      => $screening->similarity_score ? $screening->similarity_score . '%' : '-',
                'catatan_editor'  => $screening->catatan ?? '-',
                'tanggal'         => now()->format('d/m/Y H:i'),
                'app_name'        => config('app.name'),
            ]);
            $atts = $tpl->attachments;
            Mail::html($rendered['body'], function ($msg) use ($rendered, $screening, $atts) {
                $msg->to($screening->recipient_email)->subject($rendered['subject']);
                foreach ($atts as $att) {
                    if (file_exists($att->getFullPath())) {
                        $msg->attach($att->getFullPath(), ['as' => $att->original_name, 'mime' => $att->mime_type]);
                    }
                }
            });
            $screening->update(['email_sent_at' => now()]);
            return true;
        } catch (\Exception $e) {
            Log::warning('Screening email failed [' . $triggerKey . ']: ' . $e->getMessage());
            return false;
        }
    }

    private function buildChecklist(Request $request): array
    {
        $checklist = [];
        $definition = self::checklistDefinition();
        foreach ($definition as $section) {
            foreach ($section['items'] as $key => $label) {
                $val = $request->input('checklist.' . $key);
                $checklist[$key] = $val === '1' ? true : ($val === '0' ? false : null);
            }
        }
        return $checklist;
    }
}