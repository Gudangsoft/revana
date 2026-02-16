<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Artikel per Jurnal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
        .header h1 { font-size: 16px; color: #0d6efd; margin-bottom: 2px; }
        .header h2 { font-size: 12px; color: #555; font-weight: normal; }
        .header .meta { font-size: 9px; color: #888; margin-top: 5px; }

        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .summary-table td { padding: 8px 12px; text-align: center; border: 1px solid #dee2e6; }
        .summary-table .label { font-size: 8px; color: #666; text-transform: uppercase; }
        .summary-table .value { font-size: 16px; font-weight: bold; }
        .summary-table .value.blue { color: #0d6efd; }
        .summary-table .value.cyan { color: #0dcaf0; }
        .summary-table .value.orange { color: #ffc107; }
        .summary-table .value.green { color: #198754; }
        .summary-table .value.red { color: #dc3545; }

        .journal-section { margin-bottom: 15px; page-break-inside: avoid; }
        .journal-header { background: #f8f9fa; padding: 8px 12px; border: 1px solid #dee2e6; border-bottom: none; }
        .journal-header h3 { font-size: 11px; color: #0d6efd; margin-bottom: 2px; }
        .journal-header .info { font-size: 8px; color: #888; }
        .journal-header .badge-count { float: right; background: #0d6efd; color: white; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #dee2e6; padding: 4px 6px; text-align: center; }
        table.data th { background: #e9ecef; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        table.data td { font-size: 9px; }
        table.data td.left { text-align: left; }
        table.data tfoot td { background: #f8f9fa; font-weight: bold; }

        .grand-total { margin-top: 15px; border: 2px solid #0d6efd; }
        .grand-total th { background: #cfe2ff !important; color: #0d6efd; }
        .grand-total td { font-weight: bold; font-size: 11px; }

        .footer { text-align: center; margin-top: 20px; font-size: 8px; color: #aaa; border-top: 1px solid #dee2e6; padding-top: 8px; }

        .badge { display: inline-block; padding: 1px 5px; border-radius: 8px; font-size: 8px; font-weight: bold; color: white; }
        .badge-info { background: #0dcaf0; color: #000; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-success { background: #198754; }
        .badge-danger { background: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ARTIKEL PER JURNAL</h1>
        <h2>Sistem Pengelolaan Review Artikel (SIPERA)</h2>
        <div class="meta">Digenerate: {{ $generatedAt }}</div>
    </div>

    {{-- Summary --}}
    <table class="summary-table">
        <tr>
            <td>
                <div class="value blue">{{ $grandTotal['total_artikel'] }}</div>
                <div class="label">Total Artikel</div>
            </td>
            <td>
                <div class="value cyan">{{ $grandTotal['submitted'] }}</div>
                <div class="label">Submitted</div>
            </td>
            <td>
                <div class="value orange">{{ $grandTotal['in_process'] }}</div>
                <div class="label">Dalam Proses</div>
            </td>
            <td>
                <div class="value green">{{ $grandTotal['published'] }}</div>
                <div class="label">Published</div>
            </td>
            <td>
                <div class="value red">{{ $grandTotal['rejected'] }}</div>
                <div class="label">Rejected</div>
            </td>
            <td>
                <div class="value">{{ $grandTotal['total_slot'] }}</div>
                <div class="label">Total Slot</div>
            </td>
        </tr>
    </table>

    {{-- Per Journal --}}
    @foreach($reportData as $data)
    <div class="journal-section">
        <div class="journal-header">
            <span class="badge-count">{{ $data['total_artikel'] }} Artikel</span>
            <h3>{{ $data['journal']->nama_jurnal }}</h3>
            <div class="info">
                Kode: {{ $data['journal']->kode_jurnal }} |
                Publisher: {{ $data['journal']->publisher ?? '-' }} |
                Akreditasi: {{ $data['journal']->accreditation ?? '-' }} |
                Slot: {{ $data['total_slot'] }}
            </div>
        </div>
        <table class="data">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:15%">Kode Slot</th>
                    <th style="width:25%">Vol/Issue/Tahun</th>
                    <th style="width:10%">Total</th>
                    <th style="width:10%">Submitted</th>
                    <th style="width:10%">Proses</th>
                    <th style="width:10%">Published</th>
                    <th style="width:10%">Rejected</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['slots'] as $i => $slotData)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="left">{{ $slotData['slot']->kode_slot ?? '-' }}</td>
                    <td class="left">Vol. {{ $slotData['slot']->volume ?? '-' }} / No. {{ $slotData['slot']->issue ?? '-' }} / {{ $slotData['slot']->tahun ?? '-' }}</td>
                    <td><strong>{{ $slotData['total_artikel'] }}</strong></td>
                    <td>{{ $slotData['submitted'] > 0 ? $slotData['submitted'] : '-' }}</td>
                    <td>{{ $slotData['in_process'] > 0 ? $slotData['in_process'] : '-' }}</td>
                    <td>{{ $slotData['published'] > 0 ? $slotData['published'] : '-' }}</td>
                    <td>{{ $slotData['rejected'] > 0 ? $slotData['rejected'] : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="color: #999;">Belum ada slot</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;">Subtotal:</td>
                    <td>{{ $data['total_artikel'] }}</td>
                    <td>{{ $data['submitted'] }}</td>
                    <td>{{ $data['in_process'] }}</td>
                    <td>{{ $data['published'] }}</td>
                    <td>{{ $data['rejected'] }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endforeach

    {{-- Grand Total --}}
    @if(count($reportData) > 1)
    <table class="data grand-total">
        <thead>
            <tr>
                <th>GRAND TOTAL ({{ count($reportData) }} Jurnal)</th>
                <th>Total Slot</th>
                <th>Total Artikel</th>
                <th>Submitted</th>
                <th>Proses</th>
                <th>Published</th>
                <th>Rejected</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td>{{ $grandTotal['total_slot'] }}</td>
                <td style="color: #0d6efd;">{{ $grandTotal['total_artikel'] }}</td>
                <td style="color: #0dcaf0;">{{ $grandTotal['submitted'] }}</td>
                <td style="color: #ffc107;">{{ $grandTotal['in_process'] }}</td>
                <td style="color: #198754;">{{ $grandTotal['published'] }}</td>
                <td style="color: #dc3545;">{{ $grandTotal['rejected'] }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="footer">
        SIPERA - Sistem Pengelolaan Review Artikel &copy; {{ date('Y') }} | Laporan digenerate otomatis pada {{ $generatedAt }}
    </div>
</body>
</html>
