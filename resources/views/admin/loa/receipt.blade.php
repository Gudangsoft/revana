<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LOA – {{ $submission->kode_submit }}</title>
<script src="{{ asset('js/qrcode.min.js') }}"></script>
@php
    $headerImageUrl       = $headerImageUrl ?? null;
    $footerImageUrl       = $footerImageUrl ?? null;
    $accreditationLogoUrl = $accreditationLogoUrl ?? null;
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
        'criteria_note' => '[DAPAT DIREVISI OLEH REVIEWER]',
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
        'decision_note' => '[DAPAT DIREVISI OLEH REVIEWER]',
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
  body { background: #e0e0e0; font-family: 'Times New Roman', Times, serif; font-size: 10pt; }
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
  .page-inner { padding: 14mm 18mm 10mm 18mm; }
}

/* ── Print ───────────────────────────────────────────── */
@media print {
  .print-bar, .no-print { display: none !important; }
  body { margin: 0; padding: 0; font-family: 'Times New Roman', Times, serif; font-size: 10pt; }
  @page { size: A4; margin: 0; }
  .a4-page { width: 210mm; min-height: 297mm; page-break-after: always; margin: 0; }
  .page-inner { padding: 14mm 18mm 10mm 18mm; }
}

/* ── Common ─────────────────────────────────────────── */
body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #222; }

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
.jrn-name-sub { font-size: 10pt; opacity: .85; }
.jrn-issn-block { text-align: right; font-size: 8pt; line-height: 1.6; opacity: .9; }

.jrn-subbar {
  background-color: {{ $secondaryColor }};
  color: #fff; font-size: 7pt; padding: 3px 18px;
  display: flex; justify-content: space-between; align-items: center;
}

/* Document heading */
.doc-title { text-align: center; font-size: 14pt; font-weight: 900; letter-spacing: 2px; margin: 14px 0 1px; }
.doc-subtitle { text-align: center; font-size: 9pt; color: #555; margin-bottom: 4px; font-style: italic; }
.doc-no    { text-align: center; font-size: 9pt; color: #555; margin-bottom: 14px; }

/* Address block */
.to-block { margin-bottom: 12px; }
.to-block p { line-height: 1.8; font-size: 10pt; }
.hl { background: transparent; padding: 0; }

/* Body text */
.body-text { font-size: 10pt; line-height: 1.7; margin-bottom: 10px; }

/* Signature block */
.sig-block { margin-top: 20px; text-align: right; }
.sig-img { max-height: 70px; margin: 6px 0; }
.sig-name { font-weight: bold; font-size: 10pt; }
.sig-role { font-size: 9pt; color: #444; }

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
.eval-title { font-size: 13pt; font-weight: 900; text-align: center; letter-spacing: 1px; margin-bottom: 12px; }
.eval-meta table { width: 100%; font-size: 9.5pt; margin-bottom: 12px; }
.eval-meta td { padding: 2px 4px; vertical-align: top; }
.eval-meta td:first-child { width: 100px; }
.eval-meta td:nth-child(2) { width: 10px; }

.criteria-title {
  font-size: 8.5pt; font-weight: bold;
  color: {{ $primaryColor }};
  border: 1px solid {{ $primaryColor }};
  padding: 3px 8px; margin-bottom: 0;
  text-align: center;
}
.criteria-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
.criteria-table th {
  background: {{ $primaryColor }}; color: #fff;
  padding: 4px 6px; text-align: center; border: 1px solid #ccc;
  font-size: 8.5pt;
}
.criteria-table td { border: 1px solid #ccc; padding: 4px 6px; vertical-align: top; }
.criteria-table td:first-child { text-align: center; width: 28px; }

.decision-title {
  font-size: 8.5pt; font-weight: bold;
  color: {{ $primaryColor }};
  border: 1px solid {{ $primaryColor }};
  padding: 3px 8px; margin: 8px 0 0;
  text-align: center;
}
.decision-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
.decision-table td { border: 1px solid #ccc; padding: 3px 6px; vertical-align: middle; }
.chk { text-align: center; width: 28px; font-size: 11pt; }
.chk-checked { font-size: 12pt; color: #000; }
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
        @if((!empty($isAdminView) && $isAdminView) || $canEditDate)
        <label style="color:#ccc; font-size:12px; display:flex; align-items:center; gap:6px;">
            📅 Tanggal LOA:
            <input type="date" id="loa-date-picker"
                   value="{{ $loaDateRaw ?? now()->toDateString() }}"
                   style="background:#2a2a2a; color:#fff; border:1px solid #555; border-radius:4px; padding:3px 8px; font-size:12px; cursor:pointer;">
        </label>
        @endif
        @if(!empty($isAdminView) && $isAdminView)
        <a href="{{ route('admin.submissions.edit', $submission) }}"
           style="color:#90CAF9; text-decoration:none; font-size:12px;">
            ✏ Edit Afiliasi & Data
        </a>
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
        {{-- Title --}}
        <div class="doc-subtitle">{{ $L['p1_subtitle'] }}</div>
        <div class="doc-no">No. {{ $loaNumber }}</div>

        {{-- Address --}}
        @php
            $allAuthorNames = collect([$penulis])
                ->merge(collect($coAuthors)->pluck('nama'))
                ->filter()
                ->implode(', ');
        @endphp
        <div class="to-block">
            <p>{{ $L['salutation1'] }}</p>
            <p>{{ $L['salutation2'] }} &nbsp;: &nbsp;<span class="hl">{{ $allAuthorNames }}</span></p>
            <p>{{ $L['salutation3'] }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="hl">{{ $afiliasi }}</span></p>
        </div>

        {{-- Body --}}
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

        <p class="body-text">{{ $L['body3'] }}</p>

        <p class="body-text">{{ $L['body4'] }}</p>

        {{-- Signature --}}
        <div class="sig-block">
            <p>{{ $loaDate }}</p>
            <p style="margin-top:4px;">{{ $editorTitle }}</p>
            <p>{{ $jurnalNama }}</p>
            <div class="qr-wrap" id="qr1" title="{{ $L['scan_qr'] }}" style="margin-top:8px; margin-left:auto; display:inline-block;"></div>
            @if($editorName)
            <p class="sig-name" style="margin-top:6px;">{{ $editorName }}</p>
            @endif
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
            <div class="vb-row2">{{ $L['scan_qr'] }} &bull; {{ $verifyUrl ?? url('/v/' . ($submission->kode_loa ?: $submission->kode_submit)) }}</div>
            <div class="vb-row3">Doc ID: {{ $submission->kode_loa ?: $submission->kode_submit }}</div>
        </div>
        @if($accreditationLogoUrl)
        <div class="acred-logo-col">
            <img src="{{ $accreditationLogoUrl }}" style="height:44px;width:auto;object-fit:contain;" alt="Logo Akreditasi">
        </div>
        @endif
    </div>
    @if(!empty($footerImageUrl))
    <div class="footer-img-wrap">
        <img src="{{ $footerImageUrl }}" style="width:100%;display:block;" alt="Footer {{ $jurnalNama }}">
    </div>
    @endif

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
                    <th style="width:180px;">{{ $L['col_comment'] }}</th>
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
                    @if($i === 1)<span class="chk-checked">&#10003;</span>@else[&nbsp;&nbsp;&nbsp;]@endif
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
            <div class="vb-row2">{{ $L['scan_qr'] }} &bull; {{ $verifyUrl ?? url('/v/' . ($submission->kode_loa ?: $submission->kode_submit)) }}</div>
            <div class="vb-row3">Doc ID: {{ $submission->kode_loa ?: $submission->kode_submit }}</div>
        </div>
        @if($accreditationLogoUrl)
        <div class="acred-logo-col">
            <img src="{{ $accreditationLogoUrl }}" style="height:44px;width:auto;object-fit:contain;" alt="Logo Akreditasi">
        </div>
        @endif
    </div>
    @if(!empty($footerImageUrl))
    <div class="footer-img-wrap">
        <img src="{{ $footerImageUrl }}" style="width:100%;display:block;" alt="Footer {{ $jurnalNama }}">
    </div>
    @endif

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
