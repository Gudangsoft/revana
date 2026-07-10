<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:Arial,sans-serif;">
@php
    $journal = $journal ?? null;
    $primary   = $journal?->primary_color   ?? '#1A237E';
    $secondary = $journal?->secondary_color ?? '#8B6914';
@endphp
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:24px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">

      {{-- Header --}}
      <tr>
        <td style="background:{{ $primary }};padding:20px 28px;color:#fff;">
          <div style="font-size:20px;font-weight:bold;">{{ $journal?->nama_jurnal ?? 'Editorial Board' }}</div>
        </td>
      </tr>

      {{-- Body --}}
      <tr>
        <td style="padding:28px 28px 20px;">
          <p style="font-size:15px;margin:0 0 16px;">Yth. <strong>{{ $namaPembayar }}</strong>,</p>

          <p style="font-size:14px;line-height:1.7;margin:0 0 16px;">
            Berikut kami sampaikan invoice untuk:
          </p>

          <div style="background:#fffde7;border-left:4px solid {{ $secondary }};padding:12px 16px;margin:0 0 20px;font-size:14px;font-style:italic;">
            {{ $keterangan }}
          </div>

          <table width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;margin-bottom:20px;">
            <tr><td style="color:#666;width:160px;">No. Invoice</td><td>{{ $nomorInvoice }}</td></tr>
            <tr><td style="color:#666;">Jumlah</td><td><strong>Rp {{ number_format($jumlah, 0, ',', '.') }}</strong></td></tr>
            <tr><td style="color:#666;">Jatuh Tempo</td><td>{{ $tanggal->translatedFormat('d F Y') }}</td></tr>
          </table>

          <p style="font-size:13px;font-weight:bold;margin:0 0 4px;">Silakan lakukan pembayaran ke rekening berikut:</p>
          <table width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;margin-bottom:20px;background:#f8f9fa;border-radius:6px;">
            <tr><td style="color:#666;width:160px;">Bank</td><td>{{ $bankName }}</td></tr>
            <tr><td style="color:#666;">No. Rekening</td><td><strong>{{ $bankAccountNumber }}</strong></td></tr>
            <tr><td style="color:#666;">Atas Nama</td><td>{{ $bankAccountHolder }}</td></tr>
          </table>

          @if($cpMarketingNama)
          <p style="font-size:13px;color:#666;margin:0 0 8px;">
            Ada pertanyaan? Hubungi <strong>{{ $cpMarketingNama }}</strong>
            @if($cpMarketingKontak) ({{ $cpMarketingKontak }}) @endif
          </p>
          @endif

          <p style="font-size:14px;line-height:1.7;margin:16px 0 8px;">
            File PDF invoice terlampir pada email ini. Terima kasih.
          </p>
        </td>
      </tr>

      {{-- Footer --}}
      <tr>
        <td style="background:#f8f9fa;padding:16px 28px;font-size:11px;color:#999;text-align:center;">
          Email ini dikirim otomatis oleh sistem SIPERA.
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
