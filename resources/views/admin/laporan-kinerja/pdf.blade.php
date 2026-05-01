<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
    .page-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 4px; }
    .page-sub   { text-align: center; font-size: 10px; color: #475569; margin-bottom: 16px; }
    .section-title {
        background: #1e293b; color: #fff;
        padding: 5px 8px; font-size: 10px; font-weight: bold;
        margin-bottom: 0; border-radius: 3px 3px 0 0;
    }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th {
        background: #334155; color: #fff;
        padding: 4px 5px; text-align: center;
        border: 1px solid #475569; font-size: 8px;
    }
    td { padding: 3px 5px; border: 1px solid #cbd5e1; font-size: 8px; }
    tr:nth-child(even) td { background: #f8fafc; }
    .text-center { text-align: center; }
    .text-right  { text-align: right; }
    .fw-bold { font-weight: bold; }
    .foot td { background: #e2e8f0; font-weight: bold; }
    .badge-num {
        display: inline-block;
        background: #1d4ed8; color: #fff;
        border-radius: 10px; padding: 1px 5px;
        font-size: 8px;
    }
    .badge-mkt {
        display: inline-block;
        background: #0891b2; color: #fff;
        border-radius: 10px; padding: 1px 5px;
        font-size: 8px;
    }
    .summary { display: table; width: 100%; margin-bottom: 16px; }
    .sum-cell {
        display: table-cell; width: 25%;
        border: 1px solid #cbd5e1; border-radius: 4px;
        padding: 6px 8px; text-align: center; vertical-align: middle;
    }
    .sum-cell .num { font-size: 18px; font-weight: bold; }
    .sum-cell .lbl { font-size: 8px; color: #64748b; }
    .printed { text-align: right; font-size: 8px; color: #94a3b8; margin-top: 10px; }
</style>
</head>
<body>

<div class="page-title">LAPORAN KINERJA TIM</div>
<div class="page-sub">Periode: {{ $namaBulan }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}</div>

{{-- Summary --}}
<table style="margin-bottom:14px; border:none;">
    <tr>
        <td style="border:1px solid #cbd5e1; text-align:center; padding:6px; background:#eff6ff; border-radius:4px;">
            <div style="font-size:18px;font-weight:bold;color:#1d4ed8;">{{ $picRekap->count() }}</div>
            <div style="font-size:8px;color:#64748b;">PIC Aktif</div>
        </td>
        <td width="8" style="border:none;"></td>
        <td style="border:1px solid #cbd5e1; text-align:center; padding:6px; background:#f0fdf4; border-radius:4px;">
            <div style="font-size:18px;font-weight:bold;color:#16a34a;">{{ $totalPicTugas }}</div>
            <div style="font-size:8px;color:#64748b;">Total Tugas PIC</div>
        </td>
        <td width="8" style="border:none;"></td>
        <td style="border:1px solid #cbd5e1; text-align:center; padding:6px; background:#ecfeff; border-radius:4px;">
            <div style="font-size:18px;font-weight:bold;color:#0891b2;">{{ $mktRekap->count() }}</div>
            <div style="font-size:8px;color:#64748b;">Marketing Aktif</div>
        </td>
        <td width="8" style="border:none;"></td>
        <td style="border:1px solid #cbd5e1; text-align:center; padding:6px; background:#fffbeb; border-radius:4px;">
            <div style="font-size:18px;font-weight:bold;color:#d97706;">{{ $totalMktSubmit }}</div>
            <div style="font-size:8px;color:#64748b;">Total Submit Marketing</div>
        </td>
    </tr>
</table>

{{-- Tabel PIC --}}
<div class="section-title">&#128100; REKAP KINERJA PIC</div>
@if($picRekap->isEmpty())
    <p style="text-align:center;padding:12px;color:#94a3b8;">Tidak ada data.</p>
@else
<table>
    <thead>
        <tr>
            <th style="width:24px;">No</th>
            <th style="text-align:left;">Nama PIC</th>
            <th>Role</th>
            @foreach($steps as $label)
                <th>{{ $label }}</th>
            @endforeach
            <th style="background:#166534;">Total Tugas</th>
            <th style="background:#854d0e;">Total Poin</th>
        </tr>
    </thead>
    <tbody>
        @foreach($picRekap as $i => $row)
        <tr>
            <td class="text-center">{{ $i+1 }}</td>
            <td class="fw-bold">{{ $row['pic']->name }}</td>
            <td class="text-center">{{ ucfirst($row['pic']->role ?? '-') }}</td>
            @foreach($steps as $key => $label)
                <td class="text-center">
                    @if($row['step_counts'][$key] > 0)
                        <span class="badge-num">{{ $row['step_counts'][$key] }}</span>
                    @else —
                    @endif
                </td>
            @endforeach
            <td class="text-center fw-bold" style="color:#166534;">{{ $row['total_tugas'] }}</td>
            <td class="text-center fw-bold" style="color:#854d0e;">{{ $row['total_poin'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="foot">
            <td colspan="3" class="text-right">TOTAL</td>
            @foreach($steps as $key => $label)
                <td class="text-center">{{ $picRekap->sum(fn($r) => $r['step_counts'][$key]) ?: '—' }}</td>
            @endforeach
            <td class="text-center">{{ $totalPicTugas }}</td>
            <td class="text-center">{{ $totalPicPoin }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- Tabel Marketing --}}
<div class="section-title">&#128227; REKAP KINERJA MARKETING</div>
@if($mktRekap->isEmpty())
    <p style="text-align:center;padding:12px;color:#94a3b8;">Tidak ada data.</p>
@else
<table>
    <thead>
        <tr>
            <th style="width:24px;">No</th>
            <th style="text-align:left;">Nama Marketing</th>
            <th style="background:#0891b2;">Total Submit</th>
            <th style="background:#854d0e;">Total Poin</th>
        </tr>
    </thead>
    <tbody>
        @foreach($mktRekap as $i => $row)
        <tr>
            <td class="text-center">{{ $i+1 }}</td>
            <td class="fw-bold">{{ $row['marketing']->name }}</td>
            <td class="text-center"><span class="badge-mkt">{{ $row['total_submit'] }}</span></td>
            <td class="text-center fw-bold" style="color:#854d0e;">{{ $row['total_poin'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="foot">
            <td colspan="2" class="text-right">TOTAL</td>
            <td class="text-center">{{ $totalMktSubmit }}</td>
            <td class="text-center">{{ $totalMktPoin }}</td>
        </tr>
    </tfoot>
</table>
@endif

<div class="printed">Digenerate otomatis oleh sistem SIPERA</div>
</body>
</html>
