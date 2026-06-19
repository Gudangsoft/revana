<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LOA – {{ $submission->kode_submit }}</title>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
@php
    $primaryColor   = $journal?->primary_color   ?? '#1A237E';
    $secondaryColor = $journal?->secondary_color ?? '#8B6914';
    $jurnalNama     = $journal?->nama_jurnal      ?? 'Jurnal';
    $kodeSingkat    = $journal?->kode_singkat     ?? '';
    $eIssn          = $journal?->e_issn           ?? '';
    $editorName     = $journal?->editor_name      ?? '';
    $editorTitle    = $journal?->editor_title     ?? 'Editor in Chief';
    $kota           = $journal?->loa_kota         ?? 'Semarang';
    $volume         = $slot?->volume   ?? '—';
    $nomor          = $slot?->nomor    ?? '—';
    $bulan          = $slot?->bulan    ?? '—';
    $tahun          = $slot?->tahun    ?? '—';
    $penulis        = $submission->nama_penulis ?? '—';
    $afiliasi       = $submission->affiliation_penulis ?? '—';
    $judul          = $submission->judul_artikel ?? '—';
    $idArtikel      = $submission->id_artikel    ?? $submission->kode_submit;
    $articleCode    = $kodeSingkat ? $kodeSingkat . '_' . $idArtikel : $idArtikel;
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
  .print-bar { display: none !important; }
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
.doc-title { text-align: center; font-size: 14pt; font-weight: 900; letter-spacing: 2px; margin: 14px 0 2px; }
.doc-no    { text-align: center; font-size: 9pt; color: #555; margin-bottom: 14px; }

/* Address block */
.to-block { margin-bottom: 12px; }
.to-block p { line-height: 1.8; font-size: 10pt; }
.hl { background: #FFFF00; padding: 0 4px; }

/* Body text */
.body-text { font-size: 10pt; line-height: 1.7; margin-bottom: 10px; }

/* Signature block */
.sig-block { margin-top: 20px; text-align: right; }
.sig-img { max-height: 70px; margin: 6px 0; }
.sig-name { font-weight: bold; font-size: 10pt; }
.sig-role { font-size: 9pt; color: #444; }

/* Indexed footer */
.idx-bar {
  background-color: {{ $secondaryColor }};
  color: #fff; font-size: 8pt;
  padding: 6px 18px 4px;
  margin-top: 14px;
}
.idx-bar-title { font-weight: bold; margin-bottom: 4px; font-size: 8pt; }
.idx-logos { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.idx-badge {
  background: #fff; color: #333;
  padding: 2px 8px; border-radius: 3px;
  font-size: 8pt; font-weight: bold; font-family: sans-serif;
}
.verified-bar {
  background: #fff; color: #333;
  padding: 3px 18px 4px;
  font-size: 7.5pt;
  display: flex; align-items: center; gap: 8px;
}
.verified-badge {
  background: #d32f2f; color: #fff;
  padding: 1px 6px; border-radius: 2px;
  font-size: 7.5pt; font-weight: bold; font-family: sans-serif;
}

/* ── Watermark ───────────────────────────────────────── */
.a4-page { position: relative; overflow: hidden; }
.jrn-header, .jrn-subbar, .page-inner, .idx-bar, .verified-bar { position: relative; z-index: 1; }
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
.verified-bar {
  display: flex !important;
  align-items: center;
  gap: 10px;
  padding: 6px 18px !important;
}
.qr-wrap svg {
  display: block;
  width: 54px !important;
  height: 54px !important;
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

{{-- ── TOP BAR (screen only) ────────────────────────── --}}
<div class="print-bar no-print" style="font-family:sans-serif;">
    <div>
        <a href="{{ url()->previous() }}" class="btn-back">← Kembali</a>
        <span style="margin-left:16px; color:#ccc;">LOA: {{ $submission->kode_submit }}</span>
    </div>
    <div>
        <a href="{{ route('admin.submissions.edit', $submission) }}"
           style="color:#90CAF9; text-decoration:none; margin-right:16px; font-size:12px;">
            ✏ Edit Afiliasi & Data
        </a>
        <button class="btn-print" onclick="window.print()">🖨 Print / Save PDF</button>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     PAGE 1 — RECEIPT FOR PAPER
     ══════════════════════════════════════════════════════ --}}
<div class="a4-page">

    {{-- Watermark --}}
    <div class="watermark" aria-hidden="true">
        @php $wmLabel = strtoupper(($kodeSingkat ?: 'SIPERA') . ' • APRKOM • VERIFIED'); @endphp
        @for($wi = 0; $wi < 9; $wi++)
        <div class="wm-row">
            <span class="wm-text">{{ $wmLabel }}</span>
            <span class="wm-text">{{ $wmLabel }}</span>
            <span class="wm-text">{{ $wmLabel }}</span>
        </div>
        @endfor
    </div>

    {{-- Header --}}
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

    <div class="page-inner">
        {{-- Title --}}
        <div class="doc-title">RECEIPT FOR PAPER</div>
        <div class="doc-no">No. {{ $loaNumber }}</div>

        {{-- Address --}}
        <div class="to-block">
            <p>To the Honorable,</p>
            <p>Dear Sir/Madam &nbsp;: &nbsp;<span class="hl">{{ $penulis }}</span></p>
            <p>at &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="hl">{{ $afiliasi }}</span></p>
        </div>

        {{-- Body --}}
        <p class="body-text">Dear Sir or Madam,</p>
        <p class="body-text">
            We, the Editorial Board of <strong>{{ $jurnalNama }}</strong>
            @if($eIssn) with E-ISSN: <strong>{{ $eIssn }}</strong>@endif
            would like to inform you that your article titled:
        </p>

        <p class="body-text" style="text-align:center; font-style:italic; font-size:11pt;">
            &ldquo;<span class="hl">{{ $judul }}</span>&rdquo;
        </p>

        <p class="body-text">
            has been received, reviewed, <strong>ACCEPTED</strong> and will be published in
            <span class="hl">Volume {{ $volume }}, No. {{ $nomor }}, {{ $bulan }} {{ $tahun }}</span>.
        </p>

        <p class="body-text">
            We would like to express our sincere gratitude for your trust in submitting your best articles.
            We will keep you informed of the next steps in the process until publication. We look forward
            to receiving your next best article.
        </p>

        <p class="body-text">This certificate is hereby issued for use as appropriate.</p>

        {{-- Signature --}}
        <div class="sig-block">
            <p>{{ $loaDate }}</p>
            <p style="margin-top:4px;">{{ $editorTitle }}</p>
            <p>{{ $jurnalNama }}</p>
            @if($signUrl)
                <img src="{{ $signUrl }}" class="sig-img" alt="TTD">
            @else
                <div style="height:60px;"></div>
            @endif
            @if($editorName)
            <p class="sig-name">{{ $editorName }}</p>
            @endif
        </div>
    </div>

    {{-- Indexed by footer --}}
    <div class="idx-bar">
        <div class="idx-bar-title">Has been Index by:</div>
        <div class="idx-logos">
            <span class="idx-badge">Crossref</span>
            <span class="idx-badge">Google Scholar</span>
            <span class="idx-badge">Dimensions</span>
            <span class="idx-badge">SciRepID</span>
        </div>
    </div>
    <div class="verified-bar">
        <div class="qr-wrap" id="qr1" title="Scan to verify LOA authenticity"></div>
        <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span style="font-weight:bold;">Verified by</span>
                <span class="verified-badge">iThenticate</span>
                <span class="verified-badge" style="background:#1565C0;">Turnitin</span>
                <span style="font-size:6pt;color:#888;margin-left:4px;">
                    Doc ID: <strong>{{ $submission->kode_loa }}</strong>
                </span>
            </div>
            <div style="font-size:6pt;color:#888;margin-top:2px;">
                Scan QR code to verify authenticity of this document &bull;
                {{ url('/loa/' . $submission->kode_loa) }}
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════
     PAGE 2 — PAPER EVALUATION SHEET
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

    <div class="page-inner">
        <div class="eval-title">PAPER EVALUATION SHEET</div>

        {{-- Meta --}}
        <div class="eval-meta">
            <table>
                <tr>
                    <td>Author</td><td>:</td>
                    <td>{{ $penulis }}</td>
                </tr>
                <tr>
                    <td>Article Code</td><td>:</td>
                    <td><span class="hl" style="font-weight:bold;">{{ $articleCode }}</span></td>
                </tr>
                <tr>
                    <td>Title</td><td>:</td>
                    <td>
                        <span class="hl">&#10003; {{ $judul }}</span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Criteria table --}}
        <div class="criteria-title">
            EVALUATION CRITERIA
            <span style="color:{{ $secondaryColor }};">[SUBJECT TO REVISION BY THE REVIEWER]</span>
        </div>
        <table class="criteria-table">
            <thead>
                <tr>
                    <th style="width:28px;">No</th>
                    <th>Description</th>
                    <th style="width:180px;">Comments</th>
                </tr>
            </thead>
            <tbody>
                @php
                $criteria = [
                    ['Representation of article content in the <strong>Title</strong>',        'The content is relevant to the title.'],
                    ['Reflection of article content in the <strong>Abstract</strong>',         'Good. Issues, methods, and results are represented.'],
                    ['Scope of Research in <strong>Keywords</strong>',                          'Good.'],
                    ['Clarity of Research <strong>Methodology</strong>',                        'Good.'],
                    ['Presentation and Interpretation of <strong>Data</strong>',                'Good.'],
                    ['Use of <strong>Tables</strong> and <strong>Figures</strong>',             'Good.'],
                    ['Relevance of <strong>Discussion/Analysis</strong> to Research <strong>Results</strong>', 'Good.'],
                    ['Relevance of <strong>References</strong>',                                'Good.'],
                    ['Contribution to Science and Knowledge',                                   'Good.'],
                    ['<strong>Structure</strong> of the Paper',                                 'Good.'],
                    ['Use of <strong>Language</strong>',                                        'Good.'],
                ];
                @endphp
                @foreach($criteria as $i => $row)
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
            REVIEWER'S DECISION
            <span style="color:{{ $secondaryColor }};">[SUBJECT TO REVISION BY THE REVIEWER]</span>
        </div>
        <table class="decision-table">
            <tr>
                <td class="chk"><span class="chk-checked">&#10003;</span></td>
                <td>1. The article can be published as is</td>
                <td class="chk"></td>
            </tr>
            <tr>
                <td class="chk"></td>
                <td>2. The article can be published with minor revisions</td>
                <td class="chk"></td>
            </tr>
            <tr>
                <td class="chk"></td>
                <td>3. The article can be published with major revisions</td>
                <td class="chk"></td>
            </tr>
            <tr>
                <td class="chk"></td>
                <td>4. Please resubmit the article to us for re-evaluation after revisions</td>
                <td class="chk"></td>
            </tr>
            <tr>
                <td class="chk"></td>
                <td>5. The article is not suitable for publication based on the reasons above</td>
                <td class="chk">[&nbsp;&nbsp;&nbsp;]</td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="idx-bar" style="margin-top:auto;">
        <div class="idx-bar-title">Has been Index by:</div>
        <div class="idx-logos">
            <span class="idx-badge">Crossref</span>
            <span class="idx-badge">Google Scholar</span>
            <span class="idx-badge">Dimensions</span>
            <span class="idx-badge">SciRepID</span>
        </div>
    </div>
    <div class="verified-bar">
        <div class="qr-wrap" id="qr2" title="Scan to verify LOA authenticity"></div>
        <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span style="font-weight:bold;">Verified by</span>
                <span class="verified-badge">iThenticate</span>
                <span class="verified-badge" style="background:#1565C0;">Turnitin</span>
                <span style="font-size:6pt;color:#888;margin-left:4px;">
                    Doc ID: <strong>{{ $submission->kode_loa }}</strong>
                </span>
            </div>
            <div style="font-size:6pt;color:#888;margin-top:2px;">
                Scan QR code to verify authenticity of this document &bull;
                {{ url('/loa/' . $submission->kode_loa) }}
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    var verifyUrl = '{{ url("/loa/" . $submission->kode_loa) }}';

    function renderQr(elId) {
        var el = document.getElementById(elId);
        if (!el) return;
        QRCode.toString(verifyUrl, {
            type    : 'svg',
            width   : 54,
            margin  : 1,
            color   : { dark: '#111111', light: '#ffffff' }
        }, function (err, svg) {
            if (!err) el.innerHTML = svg;
        });
    }

    if (typeof QRCode !== 'undefined') {
        renderQr('qr1');
        renderQr('qr2');
    } else {
        // Fallback: load dari CDN alternatif lalu render
        var s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
        s.onload = function () { renderQr('qr1'); renderQr('qr2'); };
        document.head.appendChild(s);
    }
})();
</script>
</body>
</html>
