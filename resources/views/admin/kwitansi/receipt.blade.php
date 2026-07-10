<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Kwitansi – {{ $submission->kode_submit }}</title>
@php
    $jurnalNama = $journal?->nama_jurnal ?? 'Jurnal';
    $kodeSingkat = $journal?->kode_singkat ?? '';
    $eIssn = $journal?->e_issn ?? '';

    $sintaLevel = null;
    if ($journal && preg_match('/SINTA\s*(\d)/i', $journal->accreditation ?? '', $_sm)) {
        $sintaLevel = (int) $_sm[1];
    }
@endphp
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

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
  .edit-bar {
    background: #1e1e2e; color: #e0e0e0; font-family: sans-serif;
    padding: 16px 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;
  }
  .edit-bar label { display: block; font-size: 11px; color: #90CAF9; margin-bottom: 4px; }
  .edit-bar input, .edit-bar select, .edit-bar textarea {
    background: #2a2a3e; border: 1px solid #444; border-radius: 6px; color: #fff;
    font-size: 13px; padding: 7px 10px; box-sizing: border-box;
  }
  .a4-page {
    width: 210mm; min-height: 297mm;
    background: #fff; margin: 24px auto;
    box-shadow: 0 4px 24px rgba(0,0,0,.25);
    padding: 0;
  }
  .page-inner { padding: 10mm 18mm; }
}

@media print {
  .print-bar, .edit-bar, .no-print { display: none !important; }
  body { margin: 0; padding: 0; font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
  @page { size: A4; margin: 0; }
  .a4-page { width: 210mm; min-height: 297mm; margin: 0; }
  .page-inner { padding: 10mm 18mm; }
}

body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #222; }

.jrn-header {
  background-color: {{ $primaryColor }};
  color: #ffffff; display: table; width: 100%;
  padding: 10px 18px;
}
.jrn-logo { width: 56px; height: 56px; border-radius: 50%; border: 3px solid rgba(255,255,255,.6); object-fit: cover; display: table-cell; vertical-align: middle; }
.jrn-logo-placeholder {
  width: 56px; height: 56px; border-radius: 50%;
  background: rgba(255,255,255,.2); border: 3px solid rgba(255,255,255,.6);
  display: table-cell; vertical-align: middle; text-align: center;
  font-size: 16pt; font-weight: bold; color: #fff;
}
.jrn-title-wrap { display: table-cell; vertical-align: middle; padding: 0 14px; width: auto; }
.jrn-name-big { font-size: 16pt; font-weight: 900; letter-spacing: 1px; line-height: 1.2; }
.jrn-name-sub { font-size: 11pt; opacity: .85; }

.doc-title { text-align: center; font-size: 20pt; font-weight: 900; letter-spacing: 4px; margin: 24px 0 4px; }
.doc-no { text-align: center; font-size: 11pt; color: #555; margin-bottom: 22px; }

.kwt-table { width: 100%; border-collapse: collapse; font-size: 12pt; margin-bottom: 6px; }
.kwt-table td { padding: 5px 6px; vertical-align: top; line-height: 1.4; }
.kwt-table td:first-child { width: 160px; white-space: nowrap; }
.kwt-table td:nth-child(2) { width: 14px; }

.kwt-amount-box {
  border: 2px solid #000; border-radius: 4px;
  padding: 10px 16px; margin: 16px 0; text-align: center;
  font-size: 16pt; font-weight: 900; letter-spacing: 1px;
}
.kwt-terbilang { font-style: italic; text-align: center; margin-bottom: 18px; font-size: 11.5pt; }

.sig-block { margin-top: 40px; text-align: right; }
.sig-img { max-height: 70px; margin: 6px 0; }
.sig-name { font-weight: bold; font-size: 12pt; }
.sig-role { font-size: 11pt; color: #444; }

.sinta-bar {
  background-color: {{ $secondaryColor }}; color: #fff; font-size: 8pt;
  padding: 6px 18px; margin-top: 20px; display: table; width: 100%;
}
.sinta-badge { display: inline-flex; align-items: center; justify-content: center; background: #fff; border-radius: 4px; padding: 2px 8px; font-family: Arial, sans-serif; font-weight: 900; font-size: 13pt; }
.sinta-badge .sinta-s { color: #008000; }
.sinta-badge .sinta-n { color: #1565C0; }

.verified-bar {
  background: #f5f5f5; color: #333; padding: 6px 18px;
  font-size: 7pt; border-top: 1px solid #ddd;
}
.verified-bar strong { font-size: 8pt; }
</style>
</head>
<body>

<div class="print-bar no-print" style="font-family:sans-serif;">
    <div>
        <a href="{{ url()->previous() }}" class="btn-back">&larr; Kembali</a>
        <span style="margin-left:16px; color:#ccc;">Kwitansi: {{ $submission->kode_submit }}</span>
    </div>
    <div style="display:flex; gap:8px; align-items:center;">
        @if(isset($sendEmailRoute) && $submission->email_penulis)
        <form method="POST" action="{{ route($sendEmailRoute, $submission) }}" style="display:inline;"
              onsubmit="return confirm('Kirim kwitansi ke email {{ $submission->email_penulis }}?');">
            @csrf
            @foreach(['nama_pembayar' => $namaPembayar, 'jumlah' => $jumlah, 'keterangan' => $keterangan, 'metode_bayar' => $metodeBayar, 'tanggal' => $tanggal->toDateString()] as $field => $value)
            <input type="hidden" name="{{ $field }}" value="{{ $value }}">
            @endforeach
            <button type="submit" class="btn-back" style="background:#0078D4;">&#9993; Kirim Email</button>
        </form>
        @endif
        @if(isset($sendWaRoute) && $submission->no_hp_penulis)
        <form method="POST" action="{{ route($sendWaRoute, $submission) }}" style="display:inline;"
              onsubmit="return confirm('Kirim kwitansi via WhatsApp ke {{ $submission->no_hp_penulis }}?');">
            @csrf
            @foreach(['nama_pembayar' => $namaPembayar, 'jumlah' => $jumlah, 'keterangan' => $keterangan, 'metode_bayar' => $metodeBayar, 'tanggal' => $tanggal->toDateString()] as $field => $value)
            <input type="hidden" name="{{ $field }}" value="{{ $value }}">
            @endforeach
            <button type="submit" class="btn-back" style="background:#25D366;">&#128241; Kirim WA</button>
        </form>
        @endif
        <button class="btn-print" onclick="window.print()">&#128424; Print / Save PDF</button>
    </div>
</div>

@if(session('success'))
<div class="no-print" style="background:#1a7a4a; color:#fff; padding:10px 24px; font-family:sans-serif; font-size:13px;">
    &#10003; {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="no-print" style="background:#c62828; color:#fff; padding:10px 24px; font-family:sans-serif; font-size:13px;">
    &#9888; {{ session('error') }}
</div>
@endif

{{-- ── Form isi data pembayaran — GET, TIDAK menyimpan apapun ke database,
       cuma reload halaman ini dengan query string berbeda ────────────────── --}}
<form method="GET" class="edit-bar no-print">
    <div>
        <label>Nama Pembayar</label>
        <input type="text" name="nama_pembayar" value="{{ $namaPembayar }}" style="width:200px;">
    </div>
    <div>
        <label>Jumlah (Rp)</label>
        <input type="number" name="jumlah" value="{{ $jumlah }}" min="0" step="1000" style="width:150px;">
    </div>
    <div>
        <label>Metode Bayar</label>
        <select name="metode_bayar" style="width:150px;">
            @foreach(['Transfer Bank', 'Tunai', 'QRIS', 'Lainnya'] as $opt)
            <option value="{{ $opt }}" @selected($metodeBayar === $opt)>{{ $opt }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Tanggal</label>
        <input type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" style="width:150px;">
    </div>
    <div style="flex:1; min-width:220px;">
        <label>Keterangan / Untuk Pembayaran</label>
        <textarea name="keterangan" rows="1" style="width:100%;">{{ $keterangan }}</textarea>
    </div>
    <div>
        <button type="submit" style="padding:8px 20px; background:#2a6496; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:13px; font-weight:bold;">
            &#128190; Terapkan
        </button>
    </div>
</form>

<div class="a4-page">
    {{-- Kop surat: pakai gambar header yang sudah diupload lewat Master LOA (kalau ada),
         supaya tidak perlu input/upload ulang khusus untuk kwitansi --}}
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
            <div class="jrn-name-big">{{ $kodeSingkat ?: \Illuminate\Support\Str::words($jurnalNama, 2, '') }}</div>
            <div class="jrn-name-sub">{{ $jurnalNama }}</div>
        </div>
    </div>
    @endif

    <div class="page-inner">
        <div class="doc-title">KWITANSI</div>
        <div class="doc-no">No: {{ $nomorKwitansi }}</div>

        <table class="kwt-table">
            <tr>
                <td>Telah diterima dari</td><td>:</td>
                <td><strong>{{ $namaPembayar }}</strong></td>
            </tr>
            <tr>
                <td>Untuk pembayaran</td><td>:</td>
                <td>{{ $keterangan }}</td>
            </tr>
            <tr>
                <td>Kode Artikel</td><td>:</td>
                <td>{{ $submission->kode_submit }}</td>
            </tr>
            <tr>
                <td>Metode Pembayaran</td><td>:</td>
                <td>{{ $metodeBayar }}</td>
            </tr>
            <tr>
                <td>Tanggal</td><td>:</td>
                <td>{{ $tanggal->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <div class="kwt-amount-box">Rp {{ number_format($jumlah, 0, ',', '.') }}</div>
        <div class="kwt-terbilang">Terbilang: {{ $jumlahTerbilang }}</div>

        <div class="sig-block">
            <p>{{ $kota }}, {{ $tanggal->translatedFormat('d F Y') }}</p>
            <p class="sig-role" style="margin-top:4px;">{{ $editorTitle }}</p>
            <p>{{ $jurnalNama }}</p>
            @if($signUrl)
            <img src="{{ $signUrl }}" class="sig-img" alt="Tanda tangan">
            @endif
            @if($editorName)
            <p class="sig-name" style="margin-top:6px;">{{ $editorName }}</p>
            @endif
        </div>
    </div>

    @if($sintaLevel)
    <div class="sinta-bar">
        <span class="sinta-badge"><span class="sinta-s">S</span><span class="sinta-n">{{ $sintaLevel }}</span></span>
        <span style="font-size:8pt; font-weight:bold; padding-left:10px;">Accredited SINTA {{ $sintaLevel }}</span>
    </div>
    @endif
    <div class="verified-bar">
        <strong>Kwitansi ini digenerate otomatis dan tidak memerlukan tanda tangan basah.</strong>
    </div>
</div>

</body>
</html>
