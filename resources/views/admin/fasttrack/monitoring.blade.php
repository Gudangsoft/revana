@extends('layouts.app')

@section('title', 'Monitoring Fasttrack - ' . $appSettings['app_name'])
@section('page-title', 'Monitoring Fasttrack')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Fasttrack</h6>
                        <h2 class="mb-0">{{ $totalFasttrack }}</h2>
                    </div>
                    <i class="bi bi-lightning-charge fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Published</h6>
                        <h2 class="mb-0">{{ $publishedCount }}</h2>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Bulan Ini</h6>
                        <h2 class="mb-0">{{ $thisMonthCount }}</h2>
                    </div>
                    <i class="bi bi-calendar-month fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Tahun Ini</h6>
                        <h2 class="mb-0">{{ $thisYearCount }}</h2>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Per Marketing Stats -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <i class="bi bi-bar-chart"></i> Statistik Fasttrack per Marketing
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Marketing</th>
                        <th class="text-center">Total Fasttrack</th>
                        <th class="text-center">Bulan Ini</th>
                        <th class="text-center">Tahun Ini</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marketingStats as $index => $stat)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $stat->name }}</td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark">{{ $stat->total_fasttrack }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $stat->month_fasttrack }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $stat->year_fasttrack }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="bi bi-inbox"></i> Belum ada data
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Per PIC Stats -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <i class="bi bi-person-badge"></i> Statistik Fasttrack per PIC
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama PIC</th>
                        <th class="text-center">Total Fasttrack</th>
                        <th class="text-center">Bulan Ini</th>
                        <th class="text-center">Tahun Ini</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($picStats as $index => $stat)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $stat->name }}</td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark">{{ $stat->total_fasttrack }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $stat->month_fasttrack }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $stat->year_fasttrack }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="bi bi-inbox"></i> Belum ada data
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Fasttrack -->
<div class="card">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history"></i> Fasttrack Terbaru</span>
        <a href="{{ route('admin.fasttrack.monitoring') }}" class="btn btn-sm btn-light">
            <i class="bi bi-list"></i> Lihat Semua
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Kode</th>
                        <th>Judul</th>
                        <th>Jurnal</th>
                        <th>Marketing</th>
                        <th>PIC</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentFasttrack as $submission)
                    <tr>
                        <td>
                            <span class="badge bg-warning text-dark">{{ $submission->kode_submit }}</span>
                            @if($submission->journalSlot)
                                <br><small class="text-muted" style="font-size: 0.65rem; line-height: 1.2;" title="{{ $submission->journalSlot->display_name }}">{{ Str::limit($submission->journalSlot->journalMaster->nama_jurnal ?? '-', 20) }}<br>Vol.{{ $submission->journalSlot->volume }} No.{{ $submission->journalSlot->nomor }}</small>
                            @endif
                        </td>
                        <td>{{ Str::limit($submission->judul_artikel, 40) }}</td>
                        <td>{{ Str::limit($submission->journalSlot->journalMaster->nama_jurnal ?? '-', 30) }}</td>
                        <td>{{ $submission->marketing->name ?? '-' }}</td>
                        <td>{{ $submission->petugasSubmit->name ?? '-' }}</td>
                        <td>{{ $submission->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.fasttrack.show', $submission->id) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            <i class="bi bi-inbox"></i> Belum ada data fasttrack
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
