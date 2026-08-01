<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:Arial,sans-serif;">
@php
    $journal  = $submission->journalSlot?->journalMaster;
    $slot     = $submission->journalSlot;
    $primary  = $journal?->primary_color  ?? '#1A237E';
    $secondary= $journal?->secondary_color ?? '#8B6914';
    $loaUrl   = url('/loa/' . $submission->kode_loa);
@endphp
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:24px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">

      {{-- Header --}}
      <tr>
        <td style="background:{{ $primary }};padding:20px 28px;color:#fff;">
          <div style="font-size:20px;font-weight:bold;">{{ $journal?->nama_jurnal ?? 'Editorial Board' }}</div>
          @if($journal?->e_issn)
          <div style="font-size:12px;opacity:.8;margin-top:4px;">E-ISSN: {{ $journal->e_issn }}</div>
          @endif
        </td>
      </tr>

      {{-- Body --}}
      <tr>
        <td style="padding:28px 28px 20px;">
          <p style="font-size:15px;margin:0 0 16px;">Dear <strong>{{ $submission->nama_penulis }}</strong>,</p>

          <p style="font-size:14px;line-height:1.7;margin:0 0 16px;">
            We are pleased to inform you that your article:
          </p>

          <div style="background:#fffde7;border-left:4px solid {{ $secondary }};padding:12px 16px;margin:0 0 20px;font-size:14px;font-style:italic;">
            &ldquo;{{ $submission->judul_artikel }}&rdquo;
          </div>

          <p style="font-size:14px;line-height:1.7;margin:0 0 8px;">
            has been <strong>ACCEPTED</strong> for publication in
            <strong>{{ $journal?->nama_jurnal }}</strong>
            @if($slot)
              — Volume {{ $slot->volume }}, No. {{ $slot->nomor }}, {{ $slot->bulan }} {{ $slot->tahun }}.
            @endif
          </p>

          <p style="font-size:14px;line-height:1.7;margin:0 0 24px;">
            Your Letter of Acceptance (LOA) is ready. You can view and print it using the button below:
          </p>

          {{-- CTA --}}
          <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
            <tr>
              <td style="background:{{ $primary }};border-radius:6px;padding:0;">
                <a href="{{ $loaUrl }}"
                   style="display:inline-block;padding:12px 28px;color:#fff;text-decoration:none;font-size:14px;font-weight:bold;">
                  📄 Lihat & Print LOA
                </a>
              </td>
            </tr>
          </table>

          <p style="font-size:13px;color:#888;margin:0 0 4px;">
            Or copy this link: <a href="{{ $loaUrl }}" style="color:{{ $primary }};">{{ $loaUrl }}</a>
          </p>
        </td>
      </tr>

      {{-- Divider --}}
      <tr><td style="padding:0 28px;"><hr style="border:none;border-top:1px solid #eee;"></td></tr>

      {{-- Footer --}}
      <tr>
        <td style="background:{{ $secondary }};padding:14px 28px;color:#fff;font-size:11px;">
          <strong>{{ $journal?->editor_title ?? 'Editor in Chief' }}</strong>
          @if(($journal?->loa_show_signature ?? true) && $journal?->editor_name) &nbsp;·&nbsp; {{ $journal->editor_name }} @endif
          <br>{{ $journal?->nama_jurnal }}
          @if($journal?->link_jurnal)
          &nbsp;·&nbsp; <a href="{{ $journal->link_jurnal }}" style="color:#fff;">{{ $journal->link_jurnal }}</a>
          @endif
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
