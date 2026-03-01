<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tim Reviewer Terbanyak</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #17a2b8;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #17a2b8;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 11px;
        }
        .stats-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        .stats-box table {
            width: 100%;
        }
        .stats-box td {
            padding: 5px;
        }
        .stats-box .stat-value {
            font-weight: bold;
            color: #17a2b8;
            font-size: 14px;
        }
        table.ranking {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table.ranking th {
            background: #17a2b8;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        table.ranking th.center {
            text-align: center;
        }
        table.ranking td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        table.ranking td.center {
            text-align: center;
        }
        table.ranking tr.top-3 {
            background: #fff3cd;
        }
        table.ranking tr:nth-child(even):not(.top-3) {
            background: #f8f9fa;
        }
        .rank-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        .rank-1 { background: #ffc107; color: #000; }
        .rank-2 { background: #6c757d; color: #fff; }
        .rank-3 { background: #dc3545; color: #fff; }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .badge-primary { background: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; }
        .badge-secondary { background: #6c757d; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TIM REVIEWER TERBANYAK</h1>
        <p>Digenerate: {{ $generatedAt }} | Filter: {{ $filterInfo }}</p>
    </div>

    <div class="stats-box">
        <table>
            <tr>
                <td width="33%">
                    <small>Total Review</small><br>
                    <span class="stat-value">{{ number_format($stats['total_reviews']) }}</span>
                </td>
                <td width="33%">
                    <small>Total Reviewer</small><br>
                    <span class="stat-value">{{ number_format($stats['total_reviewers']) }}</span>
                </td>
                <td width="33%">
                    <small>Top Reviewer</small><br>
                    <span class="stat-value">{{ $stats['top_reviewer'] ? $stats['top_reviewer']->pic_name : '-' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="ranking">
        <thead>
            <tr>
                <th class="center" style="width: 50px;">Rank</th>
                <th>Nama PIC</th>
                <th class="center" style="width: 70px;">Rev. 1</th>
                <th class="center" style="width: 70px;">Rev. 2</th>
                <th class="center" style="width: 80px;">Total</th>
                <th class="center" style="width: 100px;">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviewerStats as $item)
            <tr class="{{ $item->rank <= 3 ? 'top-3' : '' }}">
                <td class="center">
                    @if($item->rank <= 3)
                        <span class="rank-badge rank-{{ $item->rank }}">{{ $item->rank }}</span>
                    @else
                        {{ $item->rank }}
                    @endif
                </td>
                <td>
                    <strong>{{ $item->pic_name }}</strong>
                    @if($item->pic && !$item->pic->is_active)
                        <small>(Nonaktif)</small>
                    @endif
                </td>
                <td class="center">
                    <span class="badge-primary">{{ number_format($item->reviewer1_count) }}</span>
                </td>
                <td class="center">
                    <span class="badge-secondary">{{ number_format($item->reviewer2_count) }}</span>
                </td>
                <td class="center"><strong>{{ number_format($item->total_review) }}</strong></td>
                <td class="center">
                    @php
                        $percentage = $stats['total_reviews'] > 0 ? ($item->total_review / $stats['total_reviews']) * 100 : 0;
                    @endphp
                    {{ number_format($percentage, 1) }}%
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">Belum ada data review</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>SIPERA - Sistem Informasi Pengelolaan Jurnal | {{ now()->format('Y') }}</p>
    </div>
</body>
</html>
