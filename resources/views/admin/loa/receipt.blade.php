<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LOA – {{ $submission->kode_submit }}</title>
<script src="{{ asset('js/qrcode.min.js') }}"></script>
@php
    $headerImageUrl       = $headerImageUrl ?? null;
    $accreditationLogoUrl = $accreditationLogoUrl ?? null;
    $linkSkAkreditasi     = $linkSkAkreditasi ?? null;
    $loaStatus            = $loaStatus ?? null;
    $pIssn                = $pIssn ?? null;
    $penerbit             = $penerbit ?? null;
    $primaryColor   = $journal?->primary_color   ?? '#1A237E';
    $secondaryColor = $journal?->secondary_color ?? '#8B6914';
    $jurnalNama     = $journal?->nama_jurnal      ?? 'Jurnal';
    $kodeSingkat    = $journal?->kode_singkat     ?? '';
    $eIssn          = $journal?->e_issn           ?? '';
    $editorName     = $journal?->editor_name      ?? '';
    $editorTitle    = $journal?->editor_title     ?? ($lang === 'id' ? 'Ketua Dewan Redaksi' : 'Editor in Chief');
    $kota           = $journal?->loa_kota         ?? 'Semarang';
    $volume         = $slot?->volume   ?? '—';
    $nomor          = $slot?->nomor    ?? '—';
    $bulan          = $slot?->bulan    ?? '—';
    $tahun          = $slot?->tahun    ?? '—';
    $penulis        = $submission->nama_penulis ?? '—';
    $afiliasi       = $submission->affiliation_penulis ?? '—';
    $coAuthors      = $submission->co_authors ?? [];
    $judul          = $submission->judul_artikel ?? '—';
    $idArtikel      = $submission->id_artikel    ?? $submission->kode_submit;
    $kodeSubmit     = $submission->kode_submit   ?? '';
    // Format: inisialJurnal_kodeArtikel_kodeSubSIPERA
    $articleCode    = implode('_', array_filter([$kodeSingkat, $idArtikel, $kodeSubmit]));

    // SINTA accreditation level (null if non-SINTA)
    $sintaLevel = null;
    if ($journal && preg_match('/SINTA\s*(\d)/i', $journal->accreditation ?? '', $_sm)) {
        $sintaLevel = (int)$_sm[1];
    }

    // ── Kamus teks bilingual ────────────────────────────────────
    $isId = ($lang ?? 'en') === 'id';

    $L = $isId ? [
        // Halaman 1 — Surat Penerimaan
        'p1_title'      => 'SURAT PENERIMAAN ARTIKEL',
        'p1_subtitle'   => 'Letter of Acceptance (LOA)',
        'salutation1'   => 'Yth.,',
        'salutation2'   => 'Bapak/Ibu',
        'salutation3'   => 'di',
        'greeting'      => 'Dengan hormat,',
        'body1_pre'     => 'Kami, Dewan Redaksi',
        'body1_issn'    => 'dengan E-ISSN:',
        'body1_post'    => 'dengan ini menyampaikan bahwa manuskrip Saudara/i yang berjudul:',
        'body2_pre'     => 'telah diterima, dinilai oleh mitra bestari, dan secara resmi',
        'body2_accepted'=> 'DITERIMA',
        'body2_post'    => 'untuk dipublikasikan pada',
        'body3'         => 'Kami menyampaikan apresiasi yang sebesar-besarnya atas kontribusi dan kepercayaan Saudara/i kepada jurnal kami. Kami akan senantiasa menginformasikan perkembangan proses penerbitan hingga artikel Saudara/i terbit. Kami mengharapkan kiriman manuskrip terbaik Saudara/i berikutnya.',
        'body4'         => 'Surat keterangan ini diterbitkan secara resmi sebagai bukti penerimaan artikel.',
        'idx_title'     => 'Telah Diindeks oleh:',
        'verified_by'   => 'Diverifikasi oleh',
        'scan_qr'       => 'Scan QR untuk verifikasi',
        'co_authors_lbl'=> 'Co-Penulis',
        // Halaman 2 — Lembar Penilaian
        'p2_title'      => 'LEMBAR PENILAIAN ARTIKEL',
        'meta_author'   => 'Penulis',
        'meta_code'     => 'Kode Artikel',
        'meta_title'    => 'Judul',
        'criteria_hdr'  => 'KRITERIA PENILAIAN',
        'criteria_note' => '',
        'col_no'        => 'No',
        'col_desc'      => 'Uraian',
        'col_comment'   => 'Komentar',
        'criteria'      => [
            ['Kesesuaian isi artikel dengan <strong>Judul</strong>',                              'Isi artikel relevan dengan judul.'],
            ['Kesesuaian isi artikel dengan <strong>Abstrak</strong>',                            'Baik. Masalah, metode, dan hasil penelitian tergambar dengan jelas.'],
            ['Cakupan penelitian pada <strong>Kata Kunci</strong>',                               'Baik.'],
            ['Kejelasan <strong>Metodologi</strong> Penelitian',                                  'Baik.'],
            ['Penyajian dan Interpretasi <strong>Data</strong>',                                  'Baik.'],
            ['Penggunaan <strong>Tabel</strong> dan <strong>Gambar</strong>',                     'Baik.'],
            ['Relevansi <strong>Pembahasan/Analisis</strong> dengan <strong>Hasil</strong> Penelitian', 'Baik.'],
            ['Relevansi <strong>Referensi</strong>',                                              'Baik.'],
            ['Kontribusi terhadap Ilmu Pengetahuan',                                              'Baik.'],
            ['<strong>Sistematika</strong> Penulisan',                                            'Baik.'],
            ['Penggunaan <strong>Bahasa</strong>',                                                'Baik.'],
        ],
        'decision_hdr'  => 'KEPUTUSAN REVIEWER',
        'decision_note' => '',
        'decisions'     => [
            'Artikel dapat diterbitkan tanpa revisi',
            'Artikel dapat diterbitkan dengan revisi kecil',
            'Artikel dapat diterbitkan dengan revisi besar',
            'Mohon kirimkan kembali artikel untuk dievaluasi ulang setelah revisi',
            'Artikel tidak layak diterbitkan berdasarkan alasan yang tercantum di atas',
        ],
    ] : [
        // Halaman 1 — Receipt for Paper
        'p1_title'      => 'RECEIPT FOR PAPER',
        'p1_subtitle'   => 'Letter of Acceptance (LOA)',
        'salutation1'   => 'To the Honorable,',
        'salutation2'   => 'Dear Sir/Madam',
        'salutation3'   => 'at',
        'greeting'      => 'Dear Sir or Madam,',
        'body1_pre'     => 'We, the Editorial Board of',
        'body1_issn'    => 'with E-ISSN:',
        'body1_post'    => 'hereby inform you that your manuscript entitled:',
        'body2_pre'     => 'has been received, peer-reviewed, and formally',
        'body2_accepted'=> 'ACCEPTED',
        'body2_post'    => 'for publication in',
        'body3'         => 'We express our sincere appreciation for your valuable contribution and trust in our journal. We will keep you informed of each subsequent step in the publication process. We look forward to receiving your future scholarly submissions.',
        'body4'         => 'This letter is officially issued as documentary proof of manuscript acceptance.',
        'idx_title'     => 'Has been Indexed by:',
        'verified_by'   => 'Verified by',
        'scan_qr'       => 'Scan QR to verify',
        'co_authors_lbl'=> 'Co-Authors',
        // Halaman 2 — Evaluation Sheet
        'p2_title'      => 'PAPER EVALUATION SHEET',
        'meta_author'   => 'Author',
        'meta_code'     => 'Article Code',
        'meta_title'    => 'Title',
        'criteria_hdr'  => 'EVALUATION CRITERIA',
        'criteria_note' => '[SUBJECT TO REVISION BY THE REVIEWER]',
        'col_no'        => 'No',
        'col_desc'      => 'Description',
        'col_comment'   => 'Comments',
        'criteria'      => [
            ['Representation of article content in the <strong>Title</strong>',                   'The content is relevant to the title.'],
            ['Reflection of article content in the <strong>Abstract</strong>',                    'Good. Issues, methods, and results are clearly represented.'],
            ['Scope of research in <strong>Keywords</strong>',                                    'Good.'],
            ['Clarity of Research <strong>Methodology</strong>',                                  'Good.'],
            ['Presentation and Interpretation of <strong>Data</strong>',                          'Good.'],
            ['Use of <strong>Tables</strong> and <strong>Figures</strong>',                       'Good.'],
            ['Relevance of <strong>Discussion/Analysis</strong> to Research <strong>Results</strong>', 'Good.'],
            ['Relevance of <strong>References</strong>',                                          'Good.'],
            ['Contribution to Science and Knowledge',                                             'Good.'],
            ['<strong>Structure</strong> of the Paper',                                           'Good.'],
            ['Use of <strong>Language</strong>',                                                  'Good.'],
        ],
        'decision_hdr'  => "REVIEWER'S DECISION",
        'decision_note' => '[SUBJECT TO REVISION BY THE REVIEWER]',
        'decisions'     => [
            'The article can be published as is',
            'The article can be published with minor revisions',
            'The article can be published with major revisions',
            'Please resubmit the article for re-evaluation after revisions',
            'The article is not suitable for publication based on the reasons stated above',
        ],
    ];
@endphp
<style>
/* ── Reset ───────────────────────────────────────────── */
* { margin: 0; padding: 0; box-sizing: border-box; }

/* ── Screen wrapper ──────────────────────────────────── */
@media screen {
  body { background: #e0e0e0; font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
  .print-bar {
    background: #222; color: #fff; padding: 10px 20px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 999;
    font-family: sans-serif; font-size: 13px;
  }
  .print-bar .btn-print {
    background: #4CAF50; color: #fff; border: none; padding: 8px 20px;
    border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold;
  }
  .print-bar .btn-back {
    background: #555; color: #fff; border: none; padding: 8px 16px;
    border-radius: 4px; cursor: pointer; font-size: 13px; text-decoration: none;
  }
  .a4-page {
    width: 210mm; min-height: 297mm;
    background: #fff; margin: 24px auto;
    box-shadow: 0 4px 24px rgba(0,0,0,.25);
    padding: 0;
    page-break-after: always;
  }
  .page-inner { padding: 3mm 18mm 10mm 18mm; }
}

/* ── Print ───────────────────────────────────────────── */
@media print {
  .print-bar, .no-print { display: none !important; }
  body { margin: 0; padding: 0; font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
  @page { size: A4; margin: 0; }
  .a4-page { width: 210mm; min-height: 297mm; page-break-after: always; margin: 0; }
  .page-inner { padding: 3mm 18mm 10mm 18mm; }
}

/* ── Common ─────────────────────────────────────────── */
body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #222; }

/* Header */
.jrn-header {
  background-color: {{ $primaryColor }};
  color: #ffffff;
  display: flex; align-items: center;
  padding: 10px 18px;
}
.jrn-logo {
  width: 64px; height: 64px; border-radius: 50%;
  border: 3px solid rgba(255,255,255,.6);
  object-fit: cover; flex-shrink: 0;
}
.jrn-logo-placeholder {
  width: 64px; height: 64px; border-radius: 50%;
  background: rgba(255,255,255,.2); border: 3px solid rgba(255,255,255,.6);
  display: flex; align-items: center; justify-content: center;
  font-size: 18pt; font-weight: bold; flex-shrink: 0; color: #fff;
}
.jrn-title-wrap { padding: 0 14px; flex: 1; }
.jrn-name-big { font-size: 17pt; font-weight: 900; letter-spacing: 1px; line-height: 1.2; }
.jrn-name-sub { font-size: 12pt; opacity: .85; }
.jrn-issn-block { text-align: right; font-size: 8pt; line-height: 1.6; opacity: .9; }

.jrn-subbar {
  background-color: {{ $secondaryColor }};
  color: #fff; font-size: 7pt; padding: 3px 18px;
  display: flex; justify-content: space-between; align-items: center;
}

/* Document heading */
.doc-title { text-align: center; font-size: 14pt; font-weight: 900; letter-spacing: 2px; margin: 14px 0 1px; }
.doc-subtitle { text-align: center; font-size: 12pt; color: #555; margin-bottom: 4px; font-style: italic; }
.doc-no    { text-align: center; font-size: 12pt; color: #555; margin-bottom: 14px; }

/* Address block */
.to-block { margin-bottom: 12px; }
.to-block p { line-height: 1; font-size: 12pt; }
.hl { background: transparent; padding: 0; }

/* Body text */
.body-text { font-size: 12pt; line-height: 1.15; margin-bottom: 4px; }

/* Journal info block */
.jrn-info-block { margin: 5px 0 6px; font-size: 12pt; }
.jrn-info-block .jrn-info-name { font-weight: bold; font-size: 12pt; margin-bottom: 4px; }
.jrn-info-block table { border-collapse: collapse; width: 100%; }
.jrn-info-block td { padding: 0px 4px 0px 0; vertical-align: top; line-height: 1.1; }
.jrn-info-block td:first-child { white-space: nowrap; min-width: 80px; }
.jrn-info-block td:nth-child(2) { width: 12px; }
.jrn-info-block a { color: inherit; }

/* Signature block */
.sig-block { margin-top: 20px; text-align: right; }
.sig-img { max-height: 70px; margin: 6px 0; }
.sig-name { font-weight: bold; font-size: 12pt; }
.sig-role { font-size: 12pt; color: #444; }

/* SINTA accreditation bar */
.sinta-bar {
  background-color: {{ $secondaryColor }};
  color: #fff; font-size: 8pt;
  padding: 6px 18px;
  margin-top: 14px;
  display: flex; align-items: center; gap: 12px;
}
.sinta-badge {
  display: inline-flex; align-items: center; justify-content: center;
  background: #fff; border-radius: 4px;
  padding: 2px 8px; gap: 1px;
  font-family: Arial, Helvetica, sans-serif;
  font-weight: 900; font-size: 13pt; line-height: 1;
  flex-shrink: 0;
}
.sinta-badge .sinta-s { color: #008000; }
.sinta-badge .sinta-n { color: #1565C0; }
.sinta-bar-text { font-size: 8pt; font-weight: bold; }

/* Accreditation logo — kolom kanan di dalam verified-bar */
.acred-logo-col {
  display: flex; align-items: center; justify-content: flex-end;
  flex-shrink: 0; padding-left: 10px;
}

/* Verified bar */
.verified-bar {
  background: #f5f5f5; color: #333;
  padding: 5px 18px !important;
  font-size: 7pt;
  display: flex !important; align-items: flex-start; gap: 10px;
  border-top: 1px solid #ddd;
}
.verified-badge {
  background: #d32f2f; color: #fff;
  padding: 1px 6px; border-radius: 2px;
  font-size: 7pt; font-weight: bold; font-family: sans-serif;
}
.verified-text-block { flex: 1; }
.verified-text-block .vb-row1 { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 2px; }
.verified-text-block .vb-row2 { font-size: 6.5pt; color: #666; }
.verified-text-block .vb-row3 { font-size: 6.5pt; color: #555; margin-top: 1px; font-weight: bold; }

/* ── Watermark ───────────────────────────────────────── */
.a4-page { position: relative; overflow: hidden; display: flex; flex-direction: column; }
.page-inner { flex: 1; }
.jrn-header, .jrn-subbar, .page-inner, .sinta-bar, .verified-bar { position: relative; z-index: 1; }
.footer-img-wrap { flex-shrink: 0; position: relative; z-index: 1; }
.watermark {
  position: absolute;
  top: -80%; left: -40%;
  width: 210%; height: 280%;
  z-index: 0;
  pointer-events: none;
  transform: rotate(-38deg);
  display: flex;
  flex-direction: column;
  justify-content: space-around;
  overflow: hidden;
}
.wm-row { white-space: nowrap; }
.wm-text {
  font-size: 16pt;
  font-weight: 900;
  color: rgba(26,35,126,.055);
  font-family: Arial, Helvetica, sans-serif;
  letter-spacing: 6px;
  text-transform: uppercase;
  padding-right: 60px;
  display: inline-block;
}

/* ── QR ──────────────────────────────────────────────── */
.qr-wrap img,
.qr-wrap canvas {
  display: block;
  width: 80px !important;
  height: 80px !important;
}

/* ── Evaluation sheet ────────────────────────────────── */
.eval-section { margin-top: 14px; }
.eval-title { font-size: 14pt; font-weight: 900; text-align: center; letter-spacing: 1px; margin-bottom: 12px; }
.eval-meta table { width: 100%; font-size: 12pt; margin-bottom: 12px; }
.eval-meta td { padding: 2px 4px; vertical-align: top; }
.eval-meta td:first-child { width: 100px; }
.eval-meta td:nth-child(2) { width: 10px; }

.criteria-title {
  font-size: 12pt; font-weight: bold;
  {{--color: {{ $primaryColor }};
  border: 1px solid {{ $primaryColor }}; --}}
  color: #000;
  border:  #000;
  padding: 3px 8px; margin-bottom: 0;
  text-align: center; 
}
.criteria-table { width: 100%; border-collapse: collapse; font-size: 12pt; }
.criteria-table th {
  {{--background: {{ $primaryColor }}; color: #fff; --}}
  background: #D3D3D3;
  color: #000;
  padding: 4px 6px; text-align: center; border: 1px solid #000;
  font-size: 12pt;
}
.criteria-table td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
.criteria-table td:first-child { text-align: center; width: 28px; }

.decision-title {
  font-size: 12pt; font-weight: bold;
 {{--color: {{ $primaryColor }};
  border: 1px solid {{ $primaryColor }};--}}
  color: #000;
  border: none;
  padding: 3px 8px; margin: 8px 0 0;
  text-align: center;
}
.decision-table { width: 100%; border-collapse: collapse; font-size: 12pt; }
.decision-table td { border: none; solid #ccc; padding: 3px 6px; vertical-align: middle; }
.chk { text-align: center; width: 28px; font-size: 12pt; }
.chk-checked { font-size: 12pt; color: #000; }

/* Article detail table (ID format) */
.detail-table { border-collapse: collapse; font-size: 12pt; margin: 4px 0; text-align:justify  }
.detail-table td { padding: 0px 6px 0px 0; vertical-align: top; line-height: 1.2; }
</style>
</head>
<body>

@php $canEditDate = $canEditDate ?? false; $backUrl = $backUrl ?? null; @endphp
{{-- ── TOP BAR (screen only) ────────────────────────── --}}
<div class="print-bar no-print" style="font-family:sans-serif;">
    <div>
        <a href="{{ $backUrl ?? url()->previous() }}" class="btn-back">← Kembali</a>
        <span style="margin-left:16px; color:#ccc;">LOA: {{ $submission->kode_submit }}</span>
    </div>
    <div style="display:flex; align-items:center; gap:12px;">
        @if((!empty($isAdminView) && $isAdminView) || $canEditDate || (auth()->check() && method_exists(auth()->user(), 'hasAdminAccess') && auth()->user()->hasAdminAccess()))
        <label style="color:#ccc; font-size:12px; display:flex; align-items:center; gap:6px;">
            📅 Tanggal LOA:
            <input type="date" id="loa-date-picker"
                   value="{{ $loaDateRaw ?? now()->toDateString() }}"
                   style="background:#2a2a2a; color:#fff; border:1px solid #555; border-radius:4px; padding:3px 8px; font-size:12px; cursor:pointer;">
        </label>
        @endif
        @if((!empty($isAdminView) && $isAdminView) || (!empty($isMarketingView) && $isMarketingView) || (auth()->check() && method_exists(auth()->user(), 'hasAdminAccess') && auth()->user()->hasAdminAccess()))
        <button onclick="document.getElementById('modal-meta-loa').style.display='flex'"
           style="background:#2a6496; color:#fff; border:none; padding:5px 14px; border-radius:4px; cursor:pointer; font-size:12px;">
            ✏ Edit Metadata LOA
        </button>
        <button onclick="document.getElementById('modal-send-loa').style.display='flex'"
           style="background:#1a7a4a; color:#fff; border:none; padding:5px 14px; border-radius:4px; cursor:pointer; font-size:12px;">
            📤 Kirim ke Author
        </button>
        @endif
        <button class="btn-print" onclick="window.print()">🖨 Print / Save PDF</button>
    </div>
</div>
<script>
document.getElementById('loa-date-picker')?.addEventListener('change', function() {
    var url = new URL(window.location.href);
    url.searchParams.set('tanggal', this.value);
    window.location.href = url.toString();
});
</script>

@if((!empty($isAdminView) && $isAdminView) || (!empty($isMarketingView) && $isMarketingView) || (auth()->check() && method_exists(auth()->user(), 'hasAdminAccess') && auth()->user()->hasAdminAccess()))
{{-- ── Modal Edit Metadata LOA (screen only, no-print) ──────────── --}}
<div id="modal-meta-loa" class="no-print"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.65); z-index:9999;
            align-items:center; justify-content:center; font-family:sans-serif;">
    <div style="background:#1e1e2e; color:#e0e0e0; border-radius:10px; width:100%; max-width:480px;
                padding:28px 32px; box-shadow:0 8px 40px rgba(0,0,0,.6); position:relative;">
        <h3 style="margin:0 0 20px; font-size:16px; color:#90CAF9;">
            ✏ Edit Metadata LOA
            <span style="font-size:12px; color:#aaa; font-weight:normal; margin-left:8px;">
                {{ $submission->kode_submit }}
            </span>
        </h3>

        <form method="POST" action="{{ (!empty($isMarketingView) && $isMarketingView)
            ? route('marketing.submissions.loa.update-metadata', $submission)
            : route('admin.submissions.loa.update-metadata', $submission) }}">
            @csrf

            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; color:#90CAF9; margin-bottom:4px;">Nama Penulis *</label>
                <input type="text" name="nama_penulis" required
                       value="{{ old('nama_penulis', $submission->nama_penulis) }}"
                       style="width:100%; padding:8px 12px; background:#2a2a3e; border:1px solid #444;
                              border-radius:6px; color:#fff; font-size:13px; box-sizing:border-box;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; color:#90CAF9; margin-bottom:4px;">Afiliasi</label>
                <textarea name="affiliation_penulis" rows="2"
                          style="width:100%; padding:8px 12px; background:#2a2a3e; border:1px solid #444;
                                 border-radius:6px; color:#fff; font-size:13px; box-sizing:border-box; resize:vertical;">{{ old('affiliation_penulis', $submission->affiliation_penulis) }}</textarea>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; color:#90CAF9; margin-bottom:4px;">Judul Artikel *</label>
                <textarea name="judul_artikel" rows="3" required
                          style="width:100%; padding:8px 12px; background:#2a2a3e; border:1px solid #444;
                                 border-radius:6px; color:#fff; font-size:13px; box-sizing:border-box; resize:vertical;">{{ old('judul_artikel', $submission->judul_artikel) }}</textarea>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:12px; color:#90CAF9; margin-bottom:4px;">
                    Tanggal LOA
                    <span style="color:#888; font-size:11px;">(kosong = tanggal hari ini)</span>
                </label>
                <input type="date" name="tanggal_loa"
                       value="{{ old('tanggal_loa', $submission->tanggal_loa?->toDateString()) }}"
                       style="width:100%; padding:8px 12px; background:#2a2a3e; border:1px solid #444;
                              border-radius:6px; color:#fff; font-size:13px; box-sizing:border-box; cursor:pointer;">
                @if($submission->tanggal_loa)
                <div style="margin-top:4px; font-size:11px; color:#f0ad4e;">
                    ⚠ Tanggal override aktif: {{ $submission->tanggal_loa->format('d M Y') }}
                </div>
                @endif
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button"
                        onclick="document.getElementById('modal-meta-loa').style.display='none'"
                        style="padding:8px 20px; background:#444; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:13px;">
                    Batal
                </button>
                <button type="submit"
                        style="padding:8px 24px; background:#2a6496; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:13px; font-weight:bold;">
                    Simpan
                </button>
            </div>
        </form>

        <button onclick="document.getElementById('modal-meta-loa').style.display='none'"
                style="position:absolute; top:12px; right:16px; background:none; border:none; color:#aaa; font-size:20px; cursor:pointer; line-height:1;">×</button>
    </div>
</div>

{{-- ── Modal Kirim LOA ke Author ──────────────────────────────────── --}}
<div id="modal-send-loa" class="no-print"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.65); z-index:9999;
            align-items:center; justify-content:center; font-family:sans-serif;">
    <div style="background:#1e1e2e; color:#e0e0e0; border-radius:10px; width:100%; max-width:500px;
                padding:28px 32px; box-shadow:0 8px 40px rgba(0,0,0,.6); position:relative;">
        <h3 style="margin:0 0 20px; font-size:16px; color:#90CAF9;">
            📤 Kirim LOA ke Author
            <span style="font-size:12px; color:#aaa; font-weight:normal; margin-left:8px;">
                {{ $submission->kode_submit }}
            </span>
        </h3>

        @php
            $loaPublicUrl = route('loa.public', ['kode_loa' => $submission->kode_loa ?: $submission->kode_submit]);
            $authorName   = $submission->nama_penulis ?? 'Yth. Penulis';
            $waMsg = "Yth. {$authorName},\n\nBerikut kami sampaikan Letter of Acceptance (LOA) untuk artikel Anda yang telah diterima di *{$jurnalNama}*.\n\nSilakan unduh/cetak LOA melalui tautan berikut:\n{$loaPublicUrl}\n\nTerima kasih atas kepercayaan Anda.\n\n_Tim Redaksi {$jurnalNama}_";
            $emailSubject = "Letter of Acceptance (LOA) – {$jurnalNama}";
            $emailBody    = "Yth. {$authorName},\n\nBerikut kami sampaikan Letter of Acceptance (LOA) untuk artikel Anda yang telah diterima di {$jurnalNama}.\n\nSilakan akses LOA Anda melalui tautan berikut:\n{$loaPublicUrl}\n\nTerima kasih atas kepercayaan Anda.\n\nSalam hormat,\nTim Redaksi {$jurnalNama}";
            $noHp    = preg_replace('/[^0-9]/', '', $submission->no_hp_penulis ?? '');
            if (str_starts_with($noHp, '0')) $noHp = '62' . substr($noHp, 1);
            $emailPenulis = $submission->email_penulis ?? '';
            $updateContactRoute = (!empty($isMarketingView) && $isMarketingView)
                ? route('marketing.submissions.loa.update-metadata', $submission)
                : route('admin.submissions.loa.update-metadata', $submission);
        @endphp

        {{-- Info author --}}
        <div style="background:#2a2a3e; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px;">
            <div style="margin-bottom:8px;">
                <span style="color:#90CAF9; font-size:11px;">Penulis</span><br>
                <strong>{{ $submission->nama_penulis ?? '—' }}</strong>
            </div>
            <div style="display:flex; gap:24px; flex-wrap:wrap;">
                <div>
                    <span style="color:#90CAF9; font-size:11px;">No HP/WA</span><br>
                    {{ $submission->no_hp_penulis ?: '—' }}
                </div>
                <div>
                    <span style="color:#90CAF9; font-size:11px;">Email</span><br>
                    {{ $emailPenulis ?: '—' }}
                </div>
            </div>
        </div>

        {{-- Form isi email/HP jika kosong --}}
        @if(!$emailPenulis || !$submission->no_hp_penulis)
        <form method="POST" action="{{ $updateContactRoute }}" style="margin-bottom:16px;">
            @csrf
            {{-- field wajib lainnya dikirim hidden agar validasi tidak gagal --}}
            <input type="hidden" name="nama_penulis"        value="{{ $submission->nama_penulis }}">
            <input type="hidden" name="judul_artikel"       value="{{ $submission->judul_artikel }}">
            <input type="hidden" name="affiliation_penulis" value="{{ $submission->affiliation_penulis }}">
            <input type="hidden" name="tanggal_loa"         value="{{ $submission->tanggal_loa?->toDateString() }}">

            <div style="font-size:11px; color:#f0ad4e; margin-bottom:8px;">
                ⚠ Lengkapi kontak author agar tombol kirim aktif:
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;">
                @if(!$emailPenulis)
                <div style="flex:1; min-width:180px;">
                    <label style="display:block; font-size:11px; color:#90CAF9; margin-bottom:3px;">Email</label>
                    <input type="email" name="email_penulis" placeholder="email@contoh.com"
                           style="width:100%; padding:7px 10px; background:#1e1e2e; border:1px solid #555;
                                  border-radius:6px; color:#fff; font-size:12px; box-sizing:border-box;">
                </div>
                @else
                <input type="hidden" name="email_penulis" value="{{ $emailPenulis }}">
                @endif

                @if(!$submission->no_hp_penulis)
                <div style="flex:1; min-width:140px;">
                    <label style="display:block; font-size:11px; color:#90CAF9; margin-bottom:3px;">No HP/WA</label>
                    <input type="text" name="no_hp_penulis" placeholder="08xxxxxxxxxx"
                           style="width:100%; padding:7px 10px; background:#1e1e2e; border:1px solid #555;
                                  border-radius:6px; color:#fff; font-size:12px; box-sizing:border-box;">
                </div>
                @else
                <input type="hidden" name="no_hp_penulis" value="{{ $submission->no_hp_penulis }}">
                @endif

                <button type="submit"
                        style="padding:7px 16px; background:#2a6496; color:#fff; border:none;
                               border-radius:6px; cursor:pointer; font-size:12px; font-weight:bold; white-space:nowrap;">
                    💾 Simpan
                </button>
            </div>
        </form>
        @endif

        {{-- Link LOA --}}
        <div style="margin-bottom:16px;">
            <label style="display:block; font-size:11px; color:#90CAF9; margin-bottom:4px;">Link LOA (untuk dikirim ke author)</label>
            <div style="display:flex; gap:6px;">
                <input type="text" id="loa-copy-link" readonly value="{{ $loaPublicUrl }}"
                       style="flex:1; padding:7px 10px; background:#2a2a3e; border:1px solid #444;
                              border-radius:6px; color:#ccc; font-size:12px; box-sizing:border-box;">
                <button onclick="copyLoaLink(event)" title="Salin link"
                        style="padding:7px 12px; background:#444; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:12px; flex-shrink:0;">
                    📋 Salin
                </button>
            </div>
        </div>

        {{-- Tombol kirim --}}
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            @if($noHp)
            <a href="https://wa.me/{{ $noHp }}?text={{ urlencode($waMsg) }}" target="_blank"
               style="flex:1; min-width:140px; display:flex; align-items:center; justify-content:center; gap:8px;
                      background:#25D366; color:#fff; padding:10px 16px; border-radius:8px;
                      text-decoration:none; font-size:13px; font-weight:bold;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Kirim via WhatsApp
            </a>
            @else
            <div style="flex:1; min-width:140px; display:flex; align-items:center; justify-content:center;
                        background:#333; color:#888; padding:10px 16px; border-radius:8px; font-size:12px;">
                ⚠ No HP tidak tersedia
            </div>
            @endif

            @if($emailPenulis)
            <a href="mailto:{{ $emailPenulis }}?subject={{ urlencode($emailSubject) }}&body={{ urlencode($emailBody) }}"
               style="flex:1; min-width:140px; display:flex; align-items:center; justify-content:center; gap:8px;
                      background:#0078D4; color:#fff; padding:10px 16px; border-radius:8px;
                      text-decoration:none; font-size:13px; font-weight:bold;">
                ✉ Kirim via Email
            </a>
            @else
            <div style="flex:1; min-width:140px; display:flex; align-items:center; justify-content:center;
                        background:#333; color:#888; padding:10px 16px; border-radius:8px; font-size:12px;">
                ⚠ Email tidak tersedia
            </div>
            @endif
        </div>

        <div style="margin-top:16px; text-align:right;">
            <button type="button"
                    onclick="document.getElementById('modal-send-loa').style.display='none'"
                    style="padding:8px 20px; background:#444; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:13px;">
                Tutup
            </button>
        </div>

        <button onclick="document.getElementById('modal-send-loa').style.display='none'"
                style="position:absolute; top:12px; right:16px; background:none; border:none; color:#aaa; font-size:20px; cursor:pointer; line-height:1;">×</button>
    </div>
</div>
<script>
function copyLoaLink(e) {
    var inp = document.getElementById('loa-copy-link');
    inp.select();
    inp.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(inp.value).then(function() {
        var btn = e.target;
        var orig = btn.textContent;
        btn.textContent = '✓ Tersalin';
        btn.style.background = '#2e7d32';
        setTimeout(function(){ btn.textContent = orig; btn.style.background = '#444'; }, 2000);
    });
}
</script>

@if(session('success'))
<script>
    // Show success notification briefly
    var _s = document.createElement('div');
    _s.style.cssText = 'position:fixed;top:60px;right:20px;background:#2e7d32;color:#fff;padding:12px 20px;border-radius:8px;font-family:sans-serif;font-size:13px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.3);';
    _s.textContent = '✓ {{ session("success") }}';
    document.body.appendChild(_s);
    setTimeout(function(){ _s.remove(); }, 4000);
</script>
@endif
@endif

{{-- ══════════════════════════════════════════════════════
     PAGE 1 — RECEIPT FOR PAPER / SURAT PENERIMAAN ARTIKEL
     ══════════════════════════════════════════════════════ --}}
<div class="a4-page">

    {{-- Watermark --}}
    <div class="watermark" aria-hidden="true">
        @php $wmLabel = strtoupper(($kodeSingkat ?: 'SIPERA') . ' • VERIFIED'); @endphp
        @for($wi = 0; $wi < 9; $wi++)
        <div class="wm-row">
            <span class="wm-text">{{ $wmLabel }}</span>
            <span class="wm-text">{{ $wmLabel }}</span>
            <span class="wm-text">{{ $wmLabel }}</span>
        </div>
        @endfor
    </div>

    {{-- Header --}}
    @if(!empty($headerImageUrl))
    <img src="{{ $headerImageUrl }}" style="width:100%;display:block;" alt="Header {{ $jurnalNama }}">
    @else
    <div class="jrn-header">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="jrn-logo" alt="Logo">
        @else
            <div class="jrn-logo-placeholder">{{ strtoupper(substr($kodeSingkat ?: $jurnalNama, 0, 2)) }}</div>
        @endif
        <div class="jrn-title-wrap">
            <div class="jrn-name-big">{{ $kodeSingkat ?: Str::words($jurnalNama, 2, '') }}</div>
            <div class="jrn-name-sub">{{ $jurnalNama }}</div>
        </div>
        <div class="jrn-issn-block">
            @if($eIssn)<div>E-ISSN: {{ $eIssn }}</div>@endif
            @if($journal?->link_jurnal)
            <div style="font-size:7pt; opacity:.8;">{{ $journal->link_jurnal }}</div>
            @endif
        </div>
    </div>
    <div class="jrn-subbar">
        <span>LPKD-APJI &bull; Jurnal Ilmiah</span>
        <span>{{ $jurnalNama }}</span>
    </div>
    @endif

    <div class="page-inner">
        @php
            $authorList     = array_values(array_filter(array_map('trim', explode(',', $penulis))));
            $firstAuthor    = $authorList[0] ?? $penulis;
            $allAuthorNames = implode(', ', $authorList) ?: $penulis;
            $kodeArtikel    = implode('/', array_filter([$kodeSingkat, $submission->kode_submit]));
        @endphp

        @if($isId)
        {{-- ── FORMAT INDONESIA ── --}}
        <table style="border-collapse:collapse; font-size:12pt; margin-bottom:16px;">
            <tr>
                <td style="padding:1px 4px 1px 0; white-space:nowrap;">No</td>
                <td style="padding:1px 10px 1px 0;">:</td>
                <td>{{ $loaNumber }}</td>
            </tr>
            <tr>
                <td style="padding:1px 4px 1px 0;">Hal</td>
                <td style="padding:1px 10px 1px 0;">:</td>
                <td><strong>Naskah Diterima</strong></td>
            </tr>
        </table>

    {{--<div class="to-block">
            <p>Kepada Yth.</p>
            <p>Sdr/i. <strong>{{ $allAuthorNames }}</strong></p>
            <p>di &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>{{ $afiliasi ?: '—' }}</strong></p>
        </div> --}}
        
        <div class="to-block">
             <table style="width:auto;">
                 <tr>
                     <td valign="top" style="width:80px; white-space:nowrap;">Kepada Yth.</td>
                     
                     <td>{{ $allAuthorNames }}</td>
                 </tr>
                 <tr>
                     <td>di</td>
                    
                     <td style="font-weight:bold;">{{ $afiliasi ?: '—' }}</td>
                 </tr>
            </table>
        </div>

        <p class="body-text">Dengan hormat,</p>
        <p class="body-text" style= "text-align:justify;">
            
            Berdasarkan hasil pemeriksaan administrasi, kesesuaian substansi, dan evaluasi yang telah dilakukan oleh Dewan Redaksi <strong>{{ $jurnalNama }}</strong>. Oleh karena itu, Dewan Redaksi memutuskan dan menyatakan bahwa naskah Saudara berikut ini:
        </p>

        <table class="detail-table">
            <colgroup><col style="width:95px;"><col style="width:12px;"><col></colgroup>
            <tr>
                <td>Judul naskah</td><td>:</td>
                <td><strong>{{ $judul }}</strong></td>
            </tr>
            <tr>
                <td>Kode naskah</td><td>:</td>
                <td>{{ $kodeArtikel }}</td>
            </tr>
        </table>

        <div style="text-align:center; font-size:14pt; font-weight:900; letter-spacing:4px; margin:10px 0 8px;">
            DITERIMA
        </div>

        <p class="body-text" style="margin-bottom:4px;">untuk diterbitkan di:</p>
        <div class="jrn-info-block">
            <div class="jrn-info-name">{{ $jurnalNama }}</div>
            <table style="border-collapse:collapse;width:100%; ">
                <colgroup><col style="width:76px;"><col style="width:12px;"><col></colgroup>
                @if($eIssn || $pIssn)
                <tr>
                    <td>ISSN</td><td>:</td>
                    <td>
                        @if($eIssn){{ $eIssn }} (E-ISSN)@endif
                        @if($eIssn && $pIssn); @endif
                        @if($pIssn){{ $pIssn }} (P-ISSN)@endif
                    </td>
                </tr>
                @endif
                <tr>
                    <td>Edisi terbit</td><td>:</td>
                    <td>Volume {{ $volume }} Nomor {{ $nomor }} (Periode {{ $tahun }}) **)</td>
                </tr>
                @if($loaStatus)
                <tr>
                    <td style="vertical-align:top;">Status</td><td style="vertical-align:top;">:</td>
                    <td style="text-align:justify;">{{ $loaStatus }}</td>
                </tr>
                @endif
                @if($penerbit)
                <tr>
                    <td>Penerbit</td><td>:</td>
                    <td>{{ $penerbit }}</td>
                </tr>
                @endif
                @if($journal?->link_jurnal)
                <tr>
                    <td>URL</td><td>:</td>
                    <td><a href="{{ $journal->link_jurnal }}" target="_blank" style="color:inherit;">{{ $journal->link_jurnal }}</a></td>
                </tr>
                @endif
            </table>
        </div>

        <p class="body-text" style="text-align:justify;">
            Kami mengucapkan selamat atas diterimanya naskah Saudara untuk dipublikasikan pada <strong>{{ $jurnalNama }}</strong>. Selanjutnya, naskah akan diproses pada tahapan editorial dan penerbitan sesuai ketentuan yang berlaku.
        </p>
        <p class="body-text" style="text-align:justify;">Atas partisipasi dan kontribusi Saudara dalam pengembangan ilmu pengetahuan, kami sampaikan terima kasih.</p>

        @else
        {{-- ── FORMAT ENGLISH ── --}}
        <div class="doc-subtitle">{{ $L['p1_subtitle'] }}</div>
        <div class="doc-no">No. {{ $loaNumber }}</div>

        <div class="to-block">
            <p>{{ $L['salutation1'] }}</p>
            <p>{{ $L['salutation2'] }} &nbsp;: &nbsp;<span class="hl">{{ $allAuthorNames }}</span></p>
            <p>{{ $L['salutation3'] }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="hl">{{ $afiliasi }}</span></p>
        </div>

        <p class="body-text">{{ $L['greeting'] }}</p>
        <p class="body-text">
            {{ $L['body1_pre'] }} <strong>{{ $jurnalNama }}</strong>
            @if($eIssn) ({{ $L['body1_issn'] }} <strong>{{ $eIssn }}</strong>)@endif,
            {{ $L['body1_post'] }}
        </p>
        <p class="body-text" style="text-align:center; font-style:italic; font-size:11pt;">
            &ldquo;<span class="hl">{{ $judul }}</span>&rdquo;
        </p>
        <p class="body-text">
            {{ $L['body2_pre'] }} <strong>{{ $L['body2_accepted'] }}</strong>
            {{ $L['body2_post'] }} <span class="hl"><em>{{ $jurnalNama }}</em>,
            Volume {{ $volume }}, No. {{ $nomor }}, {{ $bulan }} {{ $tahun }}</span>.
        </p>

        @if($eIssn || $pIssn || $loaStatus || $penerbit || $journal?->link_jurnal)
        <div class="jrn-info-block">
            <div class="jrn-info-name">{{ $jurnalNama }}</div>
            <table>
                @if($eIssn || $pIssn)
                <tr>
                    <td>ISSN</td><td>:</td>
                    <td>
                        @if($eIssn){{ $eIssn }} (online)@endif
                        @if($eIssn && $pIssn); @endif
                        @if($pIssn){{ $pIssn }} (print)@endif
                    </td>
                </tr>
                @endif
                <tr>
                    <td>Issue</td><td>:</td>
                    <td>Volume {{ $volume }}, No. {{ $nomor }} ({{ $tahun }})</td>
                </tr>
                @if($loaStatus)
                <tr><td>Status</td><td>:</td><td>{{ $loaStatus }} </td></tr>
                @endif
                @if($penerbit)
                <tr><td>Publisher</td><td>:</td><td>{{ $penerbit }}</td></tr>
                @endif
                @if($journal?->link_jurnal)
                <tr>
                    <td>URL</td><td>:</td>
                    <td><a href="{{ $journal->link_jurnal }}" target="_blank">{{ $journal->link_jurnal }}</a></td>
                </tr>
                @endif
            </table>
        </div>
        @endif

        <p class="body-text">{{ $L['body3'] }}</p>
        <p class="body-text">{{ $L['body4'] }}</p>
        @endif

        {{-- Signature --}}
        <div class="sig-block">
            <p>{{ $loaDate }}</p>
            <p style="margin-top:4px;">{{ $editorTitle }}</p>
            <p>{{ $jurnalNama }}</p>
            <div class="qr-wrap" id="qr1" title="{{ $L['scan_qr'] }}" style="margin-top:8px; margin-left:auto; display:inline-block;"></div>
            @if($signUrl)
            <img src="{{ $signUrl }}" class="sig-img" alt="Tanda tangan">
            @endif
            @if($editorName)
            <p class="sig-name" style="margin-top:6px;">{{ $editorName }}</p>
            @endif
            <div style="font-size:6.5pt; margin-top:3px; text-align:right;"> {{ $L['scan_qr'] }} </div>
        </div>
    </div>

    {{-- Footer --}}
    @if($sintaLevel)
    <div class="sinta-bar">
        <div class="sinta-badge"><span class="sinta-s">S</span><span class="sinta-n">{{ $sintaLevel }}</span></div>
        <span class="sinta-bar-text">Accredited SINTA {{ $sintaLevel }}</span>
    </div>
    @endif
    <div class="verified-bar">
        <div class="verified-text-block">
            <div class="vb-row1">
                <span style="font-weight:bold;">{{ $L['verified_by'] }}</span>
            </div>
        {{--    <div class="vb-row2">{{ $L['scan_qr'] }} &bull; {{ $verifyUrl ?? url('/v/' . ($submission->kode_loa ?: $submission->kode_submit)) }}</div> --}}
            <div class="vb-row3">Doc ID: {{ $submission->kode_loa ?: $submission->kode_submit }}</div>
        </div>
        @if($accreditationLogoUrl)
        <div class="acred-logo-col">
            @if(!empty($linkSkAkreditasi))
                <a href="{{ $linkSkAkreditasi }}" target="_blank" style="display:inline-block;">
                    <img src="{{ $accreditationLogoUrl }}" style="height:75px;width:auto;object-fit:contain;" alt="Logo Akreditasi">
                </a>
            @else
                <img src="{{ $accreditationLogoUrl }}" style="height:44px;width:auto;object-fit:contain;" alt="Logo Akreditasi">
            @endif
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════
     PAGE 2 — PAPER EVALUATION SHEET / LEMBAR PENILAIAN
     ══════════════════════════════════════════════════════ --}}
<div class="a4-page">

    {{-- Watermark --}}
    <div class="watermark" aria-hidden="true">
        @for($wi = 0; $wi < 9; $wi++)
        <div class="wm-row">
            <span class="wm-text">{{ $wmLabel }}</span>
            <span class="wm-text">{{ $wmLabel }}</span>
            <span class="wm-text">{{ $wmLabel }}</span>
        </div>
        @endfor
    </div>

    {{-- Header --}}
    @if(!empty($headerImageUrl))
    <img src="{{ $headerImageUrl }}" style="width:100%;display:block;" alt="Header {{ $jurnalNama }}">
    @else
    <div class="jrn-header">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="jrn-logo" alt="Logo">
        @else
            <div class="jrn-logo-placeholder">{{ strtoupper(substr($kodeSingkat ?: $jurnalNama, 0, 2)) }}</div>
        @endif
        <div class="jrn-title-wrap">
            <div class="jrn-name-big">{{ $kodeSingkat ?: Str::words($jurnalNama, 2, '') }}</div>
            <div class="jrn-name-sub">{{ $jurnalNama }}</div>
        </div>
        <div class="jrn-issn-block">
            @if($eIssn)<div>E-ISSN: {{ $eIssn }}</div>@endif
        </div>
    </div>
    <div class="jrn-subbar">
        <span>LPKD-APJI &bull; Jurnal Ilmiah</span>
        <span>{{ $jurnalNama }}</span>
    </div>
    @endif

    <div class="page-inner">
        <div class="eval-title">{{ $L['p2_title'] }}</div>

        {{-- Meta --}}
        <div class="eval-meta">
            <table>
                <tr>
                    <td>{{ $L['meta_author'] }}</td><td>:</td>
                    <td>{{ $allAuthorNames }}</td>
                </tr>
                <tr>
                    <td>{{ $L['meta_code'] }}</td><td>:</td>
                    <td style="font-weight:bold;">{{ $articleCode }}</td>
                </tr>
                <tr>
                    <td>{{ $L['meta_title'] }}</td><td>:</td>
                    <td>{{ $judul }}</td>
                </tr>
            </table>
        </div>

        {{-- Criteria table --}}
        <div class="criteria-title">
            {{ $L['criteria_hdr'] }}
            <span style="color:{{ $secondaryColor }};">{{ $L['criteria_note'] }}</span>
        </div>
        <table class="criteria-table">
            <thead>
                <tr>
                    <th style="width:28px;">{{ $L['col_no'] }}</th>
                    <th>{{ $L['col_desc'] }}</th>
                    <th style="width:230px;">{{ $L['col_comment'] }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($L['criteria'] as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{!! $row[0] !!}</td>
                    <td>{{ $row[1] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Reviewer's decision --}}
        <div class="decision-title">
            {{ $L['decision_hdr'] }}
            <span style="color:{{ $secondaryColor }};">{{ $L['decision_note'] }}</span>
        </div>
        <table class="decision-table">
            @foreach($L['decisions'] as $i => $dec)
            <tr>
                <td>{{ $i + 1 }}. {{ $dec }}</td>
                <td class="chk">
                    @if($i === 1)<span class="chk-checked">&#10003;</span>@else[...]@endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    {{-- Footer --}}
    @if($sintaLevel)
    <div class="sinta-bar">
        <div class="sinta-badge"><span class="sinta-s">S</span><span class="sinta-n">{{ $sintaLevel }}</span></div>
        <span class="sinta-bar-text">Accredited SINTA {{ $sintaLevel }}</span>
    </div>
    @endif
    <div class="verified-bar">
        <div class="qr-wrap" id="qr2" title="{{ $L['scan_qr'] }}"></div>
        <div class="verified-text-block">
            <div class="vb-row1">
                <span style="font-weight:bold;">{{ $L['verified_by'] }}</span>
            </div>
        {{--    <div class="vb-row2">{{ $L['scan_qr'] }} &bull; {{ $verifyUrl ?? url('/v/' . ($submission->kode_loa ?: $submission->kode_submit)) }}</div>  --}}
            <div class="vb-row3">Doc ID: {{ $submission->kode_loa ?: $submission->kode_submit }}</div>
        </div>
        @if($accreditationLogoUrl)
        <div class="acred-logo-col">
            @if(!empty($linkSkAkreditasi))
                <a href="{{ $linkSkAkreditasi }}" target="_blank" style="display:inline-block;">
                    <img src="{{ $accreditationLogoUrl }}" style="height:75px;width:auto;object-fit:contain;" alt="Logo Akreditasi">
                </a>
            @else
                <img src="{{ $accreditationLogoUrl }}" style="height:44px;width:auto;object-fit:contain;" alt="Logo Akreditasi">
            @endif
        </div>
        @endif
    </div>

</div>

<script>
(function () {
    var verifyUrl = '{{ $verifyUrl ?? url("/v/" . ($submission->kode_loa ?: $submission->kode_submit)) }}';

    function renderQr(elId) {
        var el = document.getElementById(elId);
        if (!el || typeof QRCode === 'undefined') return;
        el.innerHTML = '';
        new QRCode(el, {
            text         : verifyUrl,
            width        : 80,
            height       : 80,
            colorDark    : '#000000',
            colorLight   : '#ffffff',
            correctLevel : QRCode.CorrectLevel.L
        });
    }

    window.addEventListener('load', function () {
        renderQr('qr1');
        renderQr('qr2');
    });
})();
</script>
</body>
</html>
