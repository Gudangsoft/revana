@extends($layout ?? 'layouts.app')

@section('title', 'Laporan Artikel per Jurnal')
@section('page-title', 'Laporan Artikel per Jurnal')

@unless(auth()->guard('marketing')->check())
@section('sidebar')
    @if(auth()->guard('pic')->check())
        @include('pic.partials.sidebar')
    @else
        @include('admin.partials.sidebar')
    @endif
@endsection
@endunless

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-file-earmark-bar-graph"></i> Laporan Artikel per Jurnal</h4>
            <small class="text-muted">Digenerate: {{ $generatedAt }}</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger btn-sm">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-4 d-print-none">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Filter Jurnal</label>
                    <select name="journal_id" class="form-select">
                        <option value="">-- Semua Jurnal --</option>
                        @foreach($allJournals as $j)
                            <option value="{{ $j->id }}" {{ request('journal_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurnal }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="show_empty" value="1" class="form-check-input" id="showEmpty"
                            {{ request('show_empty') ? 'checked' : '' }}>
                        <label class="form-check-label" for="showEmpty">Tampilkan jurnal tanpa artikel</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
                @if(request()->hasAny(['journal_id', 'show_empty']))
                <div class="col-md-2">
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row row-cols-2 row-cols-md-5 g-3 mb-4">
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $grandTotal['total_artikel'] }}</h3>
                    <small class="text-muted">Total Artikel</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $grandTotal['submitted'] }}</h3>
                    <small class="text-muted">Submitted</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-warning mb-0">{{ $grandTotal['in_process'] }}</h3>
                    <small class="text-muted">Dalam Proses</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $grandTotal['published'] }}</h3>
                    <small class="text-muted">Published</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-danger mb-0">{{ $grandTotal['rejected'] }}</h3>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Table per Jurnal --}}
    @forelse($reportData as $data)
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="bi bi-journal-bookmark text-primary"></i>
                    {{ $data['journal']->nama_jurnal }}
                </h5>
                <small class="text-muted">
                    Kode: {{ $data['journal']->kode_jurnal }} |
                    Publisher: {{ $data['journal']->publisher ?? '-' }} |
                    Akreditasi: {{ $data['journal']->accreditation ?? '-' }}
                </small>
            </div>
            <div class="text-end">
                <span class="badge bg-primary fs-6">{{ $data['total_artikel'] }} Artikel</span>
                <span class="badge bg-secondary">{{ $data['total_slot'] }} Slot</span>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:5%">No</th>
                        <th>Kode Slot</th>
                        <th>Volume/Issue/Tahun</th>
                        <th class="text-center">Total Artikel</th>
                        <th class="text-center">Submitted</th>
                        <th class="text-center">Proses</th>
                        <th class="text-center">Published</th>
                        <th class="text-center">Rejected</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['slots'] as $i => $slotData)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $slotData['slot']->kode_slot ?? '-' }}</code></td>
                        <td>
                            Vol. {{ $slotData['slot']->volume ?? '-' }} /
                            No. {{ $slotData['slot']->issue ?? '-' }} /
                            {{ $slotData['slot']->tahun ?? '-' }}
                        </td>
                        <td class="text-center fw-bold">{{ $slotData['total_artikel'] }}</td>
                        <td class="text-center">
                            @if($slotData['submitted'] > 0)
                                <span class="badge bg-info">{{ $slotData['submitted'] }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($slotData['in_process'] > 0)
                                <span class="badge bg-warning text-dark">{{ $slotData['in_process'] }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($slotData['published'] > 0)
                                <span class="badge bg-success">{{ $slotData['published'] }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($slotData['rejected'] > 0)
                                <span class="badge bg-danger">{{ $slotData['rejected'] }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">Belum ada slot</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Subtotal:</td>
                        <td class="text-center">{{ $data['total_artikel'] }}</td>
                        <td class="text-center">{{ $data['submitted'] }}</td>
                        <td class="text-center">{{ $data['in_process'] }}</td>
                        <td class="text-center">{{ $data['published'] }}</td>
                        <td class="text-center">{{ $data['rejected'] }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @empty
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Tidak ada data jurnal yang ditemukan. 
        @if(!request('show_empty'))
            Coba centang "Tampilkan jurnal tanpa artikel".
        @endif
    </div>
    @endforelse

    {{-- Grand Total --}}
    @if(count($reportData) > 1)
    <div class="card shadow-sm border-primary">
        <div class="card-body">
            <table class="table table-sm mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>GRAND TOTAL ({{ count($reportData) }} Jurnal)</th>
                        <th class="text-center">Total Slot</th>
                        <th class="text-center">Total Artikel</th>
                        <th class="text-center">Submitted</th>
                        <th class="text-center">Proses</th>
                        <th class="text-center">Published</th>
                        <th class="text-center">Rejected</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="fw-bold fs-6">
                        <td></td>
                        <td class="text-center">{{ $grandTotal['total_slot'] }}</td>
                        <td class="text-center text-primary">{{ $grandTotal['total_artikel'] }}</td>
                        <td class="text-center text-info">{{ $grandTotal['submitted'] }}</td>
                        <td class="text-center text-warning">{{ $grandTotal['in_process'] }}</td>
                        <td class="text-center text-success">{{ $grandTotal['published'] }}</td>
                        <td class="text-center text-danger">{{ $grandTotal['rejected'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- Print styles --}}
<style>
    @media print {
        .d-print-none, .sidebar, .navbar, nav, footer { display: none !important; }
        .content, .col-md-10 { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
        .container-fluid { padding: 0 !important; }
        .card { break-inside: avoid; border: 1px solid #dee2e6 !important; }
        .shadow-sm { box-shadow: none !important; }
    }
</style>
@endsection
