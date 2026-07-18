<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Invoice – {{ $submission->kode_submit }}</title>
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
    padding: 0; position: relative;
  }
  .page-inner { padding: 10mm 18mm; }
}

@media print {
  .print-bar, .edit-bar, .no-print { display: none !important; }
  body { margin: 0; padding: 0; font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
  @page { size: A4; margin: 0; }
  .a4-page { width: 210mm; min-height: 297mm; margin: 0; position: relative; }
  .page-inner { padding: 10mm 18mm; }
}

/* Footer (badge SINTA + catatan verifikasi) dipin ke BAWAH halaman lewat
   position:absolute, bukan cuma mengikuti alur dokumen — kwitansi/invoice jauh
   lebih pendek dari 1 halaman penuh, jadi tanpa ini footer akan "mengambang" di
   tengah halaman (persis sesudah konten berakhir), bukan di posisi footer yang
   wajar. dompdf mendukung position:absolute (sudah dipakai juga di watermark LOA). */
.page-footer { position: absolute; left: 0; bottom: 0; width: 100%; }

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

.bank-box {
  border: 1px solid #999; border-radius: 4px;
  padding: 10px 14px; margin: 0 0 16px;
}
.bank-box .bank-title { font-weight: bold; font-size: 11pt; margin-bottom: 6px; }

.cp-line { font-size: 10.5pt; color: #444; margin-bottom: 16px; }

.sig-block { margin-top: 30px; text-align: right; }
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
        <span style="margin-left:16px; color:#ccc;">Invoice: {{ $submission->kode_submit }}</span>
    </div>
    <div style="display:flex; gap:8px; align-items:center;">
        @if(isset($sendEmailRoute))
        <button type="button" onclick="document.getElementById('modal-send-invoice').style.display='flex'"
                style="background:#1a7a4a; color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; font-size:13px; font-weight:bold;">
            &#128228; Kirim ke Author
        </button>
        @endif
        <button class="btn-print" onclick="window.print()">&#128424; Print / Save PDF</button>
    </div>
</div>

{{-- ── Modal Kirim Invoice ke Author (mirip modal LOA/Kwitansi) ───────────
       Field invoice (nama_pembayar, jumlah, rekening, cp marketing, dst) dibawa
       sebagai hidden input dari halaman ini persis apa adanya, TIDAK disimpan
       ke database — cuma kontak (email/HP) yang boleh diedit & disimpan di
       sini, karena kontak memang sudah jadi bagian data Submission sejak awal. --}}
@if(isset($sendEmailRoute))
@php
    $invParams = [
        'nama_pembayar' => $namaPembayar, 'jumlah' => $jumlah, 'keterangan' => $keterangan,
        'metode_bayar' => $metodeBayar, 'tanggal' => $tanggal->toDateString(),
        'bank_name' => $bankName, 'bank_account_number' => $bankAccountNumber, 'bank_account_holder' => $bankAccountHolder,
        'cp_marketing_nama' => $cpMarketingNama, 'cp_marketing_kontak' => $cpMarketingKontak,
    ];
@endphp
<div id="modal-send-invoice" class="no-print"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.65); z-index:9999;
            align-items:center; justify-content:center; font-family:sans-serif;">
    <div style="background:#1e1e2e; color:#e0e0e0; border-radius:10px; width:100%; max-width:500px;
                padding:28px 32px; box-shadow:0 8px 40px rgba(0,0,0,.6); position:relative;">
        <h3 style="margin:0 0 20px; font-size:16px; color:#90CAF9;">
            &#128228; Kirim Invoice ke Author
            <span style="font-size:12px; color:#aaa; font-weight:normal; margin-left:8px;">{{ $submission->kode_submit }}</span>
        </h3>

        {{-- Info author --}}
        <div style="background:#2a2a3e; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px;">
            <div style="margin-bottom:8px;">
                <span style="color:#90CAF9; font-size:11px;">Ditagihkan kepada</span><br>
                <strong>{{ $namaPembayar }}</strong>
            </div>
        </div>

        {{-- Form edit kontak (email/HP) — INI yang disimpan ke DB, terpisah dari data invoice --}}
        <form method="POST" action="{{ route($updateContactRoute, $submission) }}" style="margin-bottom:16px;">
            @csrf
            @foreach($invParams as $field => $value)
            <input type="hidden" name="{{ $field }}" value="{{ $value }}">
            @endforeach

            <div style="font-size:11px; color:{{ (!$submission->email_penulis || !$submission->no_hp_penulis) ? '#f0ad4e' : '#888' }}; margin-bottom:8px;">
                @if(!$submission->email_penulis || !$submission->no_hp_penulis)
                &#9888; Lengkapi kontak author agar tombol kirim aktif:
                @else
                Ubah kontak author bila perlu:
                @endif
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;">
                <div style="flex:1; min-width:180px;">
                    <label style="display:block; font-size:11px; color:#90CAF9; margin-bottom:3px;">Email</label>
                    <input type="email" name="email_penulis" placeholder="email@contoh.com"
                           value="{{ $submission->email_penulis }}"
                           style="width:100%; padding:7px 10px; background:#1e1e2e; border:1px solid #555;
                                  border-radius:6px; color:#fff; font-size:12px; box-sizing:border-box;">
                </div>
                <div style="flex:1; min-width:140px;">
                    <label style="display:block; font-size:11px; color:#90CAF9; margin-bottom:3px;">No HP/WA</label>
                    <input type="text" name="no_hp_penulis" placeholder="08xxxxxxxxxx"
                           value="{{ $submission->no_hp_penulis }}"
                           style="width:100%; padding:7px 10px; background:#1e1e2e; border:1px solid #555;
                                  border-radius:6px; color:#fff; font-size:12px; box-sizing:border-box;">
                </div>
                <button type="submit"
                        style="padding:7px 16px; background:#2a6496; color:#fff; border:none;
                               border-radius:6px; cursor:pointer; font-size:12px; font-weight:bold; white-space:nowrap;">
                    &#128190; Simpan
                </button>
            </div>
        </form>

        {{-- Link PDF publik --}}
        <div style="margin-bottom:16px;">
            <label style="display:block; font-size:11px; color:#90CAF9; margin-bottom:4px;">Link PDF Invoice (untuk dikirim ke author)</label>
            <div style="display:flex; gap:6px;">
                <input type="text" id="inv-copy-link" readonly value="{{ $publicPdfUrl }}"
                       style="flex:1; padding:7px 10px; background:#2a2a3e; border:1px solid #444;
                              border-radius:6px; color:#ccc; font-size:12px; box-sizing:border-box;">
                <button onclick="copyInvLink(event)" title="Salin link"
                        style="padding:7px 12px; background:#444; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:12px; flex-shrink:0;">
                    &#128203; Salin
                </button>
            </div>
        </div>

        {{-- Tombol kirim --}}
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            @if($submission->no_hp_penulis)
            <form method="POST" action="{{ route($sendWaRoute, $submission) }}" style="flex:1; min-width:140px;"
                  onsubmit="return confirm('Kirim invoice via WhatsApp ke {{ $submission->no_hp_penulis }}?');">
                @csrf
                @foreach($invParams as $field => $value)
                <input type="hidden" name="{{ $field }}" value="{{ $value }}">
                @endforeach
                <button type="submit"
                   style="width:100%; display:flex; align-items:center; justify-content:center; gap:8px;
                          background:#25D366; color:#fff; padding:10px 16px; border-radius:8px; border:none;
                          cursor:pointer; font-size:13px; font-weight:bold;">
                    &#128241; Kirim via WhatsApp
                </button>
            </form>
            @else
            <div style="flex:1; min-width:140px; display:flex; align-items:center; justify-content:center;
                        background:#333; color:#888; padding:10px 16px; border-radius:8px; font-size:12px;">
                &#9888; No HP tidak tersedia
            </div>
            @endif

            @if($submission->email_penulis)
            <form method="POST" action="{{ route($sendEmailRoute, $submission) }}" style="flex:1; min-width:140px;"
                  onsubmit="return confirm('Kirim invoice ke email {{ $submission->email_penulis }}?');">
                @csrf
                @foreach($invParams as $field => $value)
                <input type="hidden" name="{{ $field }}" value="{{ $value }}">
                @endforeach
                <button type="submit"
                   style="width:100%; display:flex; align-items:center; justify-content:center; gap:8px;
                          background:#0078D4; color:#fff; padding:10px 16px; border-radius:8px; border:none;
                          cursor:pointer; font-size:13px; font-weight:bold;">
                    &#9993; Kirim via Email
                </button>
            </form>
            @else
            <div style="flex:1; min-width:140px; display:flex; align-items:center; justify-content:center;
                        background:#333; color:#888; padding:10px 16px; border-radius:8px; font-size:12px;">
                &#9888; Email tidak tersedia
            </div>
            @endif
        </div>

        <div style="margin-top:16px; text-align:right;">
            <button type="button"
                    onclick="document.getElementById('modal-send-invoice').style.display='none'"
                    style="padding:8px 20px; background:#444; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:13px;">
                Tutup
            </button>
        </div>

        <button onclick="document.getElementById('modal-send-invoice').style.display='none'"
                style="position:absolute; top:12px; right:16px; background:none; border:none; color:#aaa; font-size:20px; cursor:pointer; line-height:1;">&times;</button>
    </div>
</div>
<script>
function copyInvLink(e) {
    var inp = document.getElementById('inv-copy-link');
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
@endif

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

{{-- ── Form isi data invoice — GET, TIDAK menyimpan apapun ke database,
       cuma reload halaman ini dengan query string berbeda. Info rekening
       & CP Marketing sudah terisi otomatis (dari Master Invoice / akun
       marketing yang login) tapi tetap bisa diubah manual di sini. ────── --}}
<form method="GET" class="edit-bar no-print">
    <div>
        <label>Nama Pembayar</label>
        <input type="text" name="nama_pembayar" value="{{ $namaPembayar }}" style="width:180px;">
    </div>
    <div>
        <label>Jumlah (Rp)</label>
        <input type="number" name="jumlah" value="{{ $jumlah }}" min="0" step="1000" style="width:130px;">
    </div>
    <div>
        <label>Tanggal</label>
        <input type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" style="width:150px;">
    </div>
    <div style="flex:1; min-width:200px;">
        <label>Keterangan</label>
        <textarea name="keterangan" rows="1" style="width:100%;">{{ $keterangan }}</textarea>
    </div>
    <div>
        <label>Bank</label>
        <input type="text" name="bank_name" value="{{ $bankName }}" style="width:130px;" placeholder="mis. BCA">
    </div>
    <div>
        <label>No Rekening</label>
        <input type="text" name="bank_account_number" value="{{ $bankAccountNumber }}" style="width:150px;">
    </div>
    <div>
        <label>Atas Nama Rekening</label>
        <input type="text" name="bank_account_holder" value="{{ $bankAccountHolder }}" style="width:170px;">
    </div>
    <div>
        <label>CP Marketing</label>
        <input type="text" name="cp_marketing_nama" value="{{ $cpMarketingNama }}" style="width:150px;">
    </div>
    <div>
        <label>Kontak CP Marketing</label>
        <input type="text" name="cp_marketing_kontak" value="{{ $cpMarketingKontak }}" style="width:150px;">
    </div>
    <div>
        <button type="submit" style="padding:8px 20px; background:#2a6496; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:13px; font-weight:bold;">
            &#128190; Terapkan
        </button>
    </div>
</form>

<div class="a4-page">
    {{-- Kop surat: pakai gambar header yang sudah diupload lewat Master LOA (kalau ada),
         supaya tidak perlu input/upload ulang khusus untuk invoice --}}
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
        <div class="doc-title">INVOICE</div>
        <div class="doc-no">No: {{ $nomorInvoice }}</div>

        <table class="kwt-table">
            <tr>
                <td>Ditagihkan kepada</td><td>:</td>
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
                <td>Tanggal Invoice</td><td>:</td>
                <td>{{ $tanggal->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <div class="kwt-amount-box">Rp {{ number_format($jumlah, 0, ',', '.') }}</div>
        <div class="kwt-terbilang">Terbilang: {{ $jumlahTerbilang }}</div>

        <div class="bank-box">
            <div class="bank-title">Instruksi Pembayaran</div>
            <table class="kwt-table" style="margin:0;">
                <tr><td style="width:110px;">Bank</td><td>:</td><td>{{ $bankName }}</td></tr>
                <tr><td>No. Rekening</td><td>:</td><td><strong>{{ $bankAccountNumber }}</strong></td></tr>
                <tr><td>Atas Nama</td><td>:</td><td>{{ $bankAccountHolder }}</td></tr>
                <tr><td>Metode Bayar</td><td>:</td><td>{{ $metodeBayar }}</td></tr>
            </table>
        </div>

        @if($cpMarketingNama)
        <div class="cp-line">
            Ada pertanyaan mengenai invoice ini? Hubungi <strong>{{ $cpMarketingNama }}</strong>
            @if($cpMarketingKontak) ({{ $cpMarketingKontak }}) @endif
        </div>
        @endif

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

    <div class="page-footer">
        @if($sintaLevel)
        <div class="sinta-bar">
            <span class="sinta-badge"><span class="sinta-s">S</span><span class="sinta-n">{{ $sintaLevel }}</span></span>
            <span style="font-size:8pt; font-weight:bold; padding-left:10px;">Accredited SINTA {{ $sintaLevel }}</span>
        </div>
        @endif
        <div class="verified-bar">
            <strong>Invoice ini digenerate otomatis dan tidak memerlukan tanda tangan basah.</strong>
        </div>
    </div>
</div>

</body>
</html>
