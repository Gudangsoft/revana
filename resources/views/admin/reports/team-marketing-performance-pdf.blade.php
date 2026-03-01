<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Tim Marketing Terbanyak</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #dc3545;
        }
        .header h1 {
            font-size: 16pt;
            color: #dc3545;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 9pt;
        }
        .process-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
            margin-top: 5px;
        }
        .process-all {
            background: #0d6efd;
            color: white;
        }
        .process-normal {
            background: #198754;
            color: white;
        }
        .process-fasttrack {
            background: #dc3545;
            color: white;
        }
        .info-section {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-box {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: linear-gradient(to bottom, #dc3545, #b02a37);
            color: white;
        }
        .stats-box h3 {
            font-size: 18pt;
            margin-bottom: 5px;
        }
        .stats-box p {
            font-size: 8pt;
            opacity: 0.9;
        }
        table.ranking {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.ranking th {
            background: #dc3545;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 9pt;
        }
        table.ranking td {
            padding: 6px 10px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }
        table.ranking tr:nth-child(even) {
            background: #f9f9f9;
        }
        table.ranking tr.top-3 {
            background: #fff3cd;
        }
        .rank-badge {
            display: inline-block;
            width: 25px;
            height: 25px;
            line-height: 25px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 10pt;
        }
        .rank-1 {
            background: #ffc107;
            color: #856404;
        }
        .rank-2 {
            background: #6c757d;
            color: white;
        }
        .rank-3 {
            background: #dc3545;
            color: white;
        }
        .rank-normal {
            color: #666;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Tim Marketing Terbanyak</h1>
        <p>SIPERA - Sistem Informasi Penerbitan Jurnal</p>
        <span class="process-badge @if($processType == 'all') process-all @elseif($processType == 'normal') process-normal @else process-fasttrack @endif">
            {{ $processType == 'all' ? 'Semua Jalur' : ($processType == 'normal' ? 'Jalur Normal' : 'Jalur Fasttrack') }}
        </span>
    </div>

    <div class="info-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <strong>Dicetak oleh:</strong> {{ Auth::user()->name ?? 'Admin' }}
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Tanggal:</strong> {{ now()->format('d F Y H:i') }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Periode:</strong> 
                    @if(request('tanggal_dari') && request('tanggal_sampai'))
                        {{ \Carbon\Carbon::parse(request('tanggal_dari'))->format('d F Y') }} - 
                        {{ \Carbon\Carbon::parse(request('tanggal_sampai'))->format('d F Y') }}
                    @elseif(request('tanggal_dari'))
                        Dari {{ \Carbon\Carbon::parse(request('tanggal_dari'))->format('d F Y') }}
                    @elseif(request('tanggal_sampai'))
                        Sampai {{ \Carbon\Carbon::parse(request('tanggal_sampai'))->format('d F Y') }}
                    @else
                        Semua Periode
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="stats-grid">
        <div class="stats-box">
            <h3>{{ number_format($stats['total_tasks']) }}</h3>
            <p>Total Submission Marketing</p>
        </div>
        <div class="stats-box">
            <h3>{{ number_format($stats['total_marketing']) }}</h3>
            <p>Total Marketing</p>
        </div>
        <div class="stats-box">
            <h3>{{ $stats['top_marketing'] ? $stats['top_marketing']->total_task : 0 }}</h3>
            <p>Submission Tertinggi</p>
        </div>
    </div>

    <table class="ranking">
        <thead>
            <tr>
                <th style="width: 60px; text-align: center;">Rank</th>
                <th>Nama Marketing</th>
                <th style="width: 80px; text-align: center;">Total</th>
                <th style="width: 70px; text-align: center;">Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($marketingRankings as $item)
            <tr class="{{ $item->rank <= 3 ? 'top-3' : '' }}">
                <td class="text-center">
                    @if($item->rank == 1)
                        <span class="rank-badge rank-1">1</span>
                    @elseif($item->rank == 2)
                        <span class="rank-badge rank-2">2</span>
                    @elseif($item->rank == 3)
                        <span class="rank-badge rank-3">3</span>
                    @else
                        <span class="rank-normal">{{ $item->rank }}</span>
                    @endif
                </td>
                <td>
                    {{ $item->name }}
                    @if($item->model && !$item->model->is_active)
                        <span style="color: #999; font-size: 8pt;">(Nonaktif)</span>
                    @endif
                </td>
                <td class="text-center">
                    <strong>{{ number_format($item->total_task) }}</strong>
                </td>
                <td class="text-center">
                    <strong>{{ number_format($item->completed_task ?? 0) }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="padding: 20px; color: #999;">
                    Belum ada data
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh SIPERA pada {{ now()->format('d F Y H:i:s') }}</p>
        <p>Total {{ $marketingRankings->count() }} Marketing tercatat</p>
    </div>
</body>
</html>
