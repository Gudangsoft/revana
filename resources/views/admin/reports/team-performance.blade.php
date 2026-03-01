@extends('layouts.app')

@section('title', 'Laporan Performa Tim ' . ($step === 'marketing' ? 'Marketing' : 'PIC'))
@section('page-title', 'Laporan Performa Tim ' . ($step === 'marketing' ? 'Marketing' : 'PIC'))

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
{{-- Process Type Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card {{ $processType == 'all' ? 'border-primary border-2' : '' }} h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-muted">Semua Jalur</h6>
                        <h3 class="mb-0 fw-bold text-primary">{{ number_format($stats['total_all']) }}</h3>
                    </div>
                    <a href="{{ route('admin.team-performance', array_merge(request()->except('process_type'), ['process_type' => 'all', 'step' => $step])) }}" 
                       class="btn btn-sm {{ $processType == 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi bi-list"></i> Lihat
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card {{ $processType == 'normal' ? 'border-success border-2' : '' }} h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-muted">Jalur Normal</h6>
                        <h3 class="mb-0 fw-bold text-success">{{ number_format($stats['total_normal']) }}</h3>
                    </div>
                    <a href="{{ route('admin.team-performance', array_merge(request()->except('process_type'), ['process_type' => 'normal', 'step' => $step])) }}" 
                       class="btn btn-sm {{ $processType == 'normal' ? 'btn-success' : 'btn-outline-success' }}">
                        <i class="bi bi-check-circle"></i> Lihat
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card {{ $processType == 'fasttrack' ? 'border-danger border-2' : '' }} h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-muted">Jalur Fasttrack</h6>
                        <h3 class="mb-0 fw-bold text-danger">{{ number_format($stats['total_fasttrack']) }}</h3>
                    </div>
                    <a href="{{ route('admin.team-performance', array_merge(request()->except('process_type'), ['process_type' => 'fasttrack', 'step' => $step])) }}" 
                       class="btn btn-sm {{ $processType == 'fasttrack' ? 'btn-danger' : 'btn-outline-danger' }}">
                        <i class="bi bi-rocket"></i> Lihat
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Step Navigation Tabs --}}
<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted me-2"><i class="bi bi-people"></i> PIC:</span>
            @foreach($stepConfigs as $stepKey => $stepConfig)
                @if(!isset($stepConfig['is_marketing']))
                <a href="{{ route('admin.team-performance', array_merge(request()->except('step'), ['step' => $stepKey])) }}" 
                   class="btn btn-sm {{ $step == $stepKey ? 'btn-'.$stepConfig['color'] : 'btn-outline-'.$stepConfig['color'] }}">
                    <i class="bi bi-{{ $stepConfig['icon'] }}"></i> {{ $stepConfig['title'] }}
                </a>
                @endif
            @endforeach
            <span class="border-start ps-2 ms-2"></span>
            <span class="text-muted me-2"><i class="bi bi-megaphone"></i> Marketing:</span>
            @if(isset($stepConfigs['marketing']))
            <a href="{{ route('admin.team-performance', array_merge(request()->except('step'), ['step' => 'marketing'])) }}" 
               class="btn btn-sm {{ $step == 'marketing' ? 'btn-'.$stepConfigs['marketing']['color'] : 'btn-outline-'.$stepConfigs['marketing']['color'] }}">
                <i class="bi bi-{{ $stepConfigs['marketing']['icon'] }}"></i> {{ $stepConfigs['marketing']['title'] }}
            </a>
            @endif
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel"></i> Filter Laporan
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="step" value="{{ $step }}">
            <input type="hidden" name="process_type" value="{{ $processType }}">
            <div class="col-md-4">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('admin.team-performance', ['step' => $step, 'process_type' => $processType]) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
                <a href="{{ route('admin.team-performance', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="btn btn-danger">
                    <i class="bi bi-file-pdf"></i> PDF
                </a>
                <a href="{{ route('admin.team-performance', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
            </div>
        </form>
        <hr class="my-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('admin.team-performance', array_merge(request()->query(), ['export' => 'excel_all'])) }}" class="btn btn-info text-white">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Seluruh Rekap (Excel)
            </a>
            @if($step === 'marketing')
            <form method="POST" action="{{ route('admin.sync.marketing-points') }}" class="d-inline" onsubmit="return confirm('Sinkronisasi total point semua Marketing berdasarkan jumlah submission?\n\nProses ini akan menghitung ulang total_points semua Marketing.')">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-arrow-repeat"></i> Sinkronisasi Point Marketing
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.sync.pic-points') }}" class="d-inline" onsubmit="return confirm('Sinkronisasi total point semua PIC berdasarkan riwayat tugas?\n\nProses ini akan menghitung ulang total_points semua PIC.')">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-arrow-repeat"></i> Sinkronisasi Point PIC
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

{{-- Statistics --}}
@php $isMarketing = $step === 'marketing'; @endphp
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card bg-{{ $config['color'] }} text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">{{ $isMarketing ? 'Total Submission' : 'Total Tugas' }} {{ $config['title'] }}</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_tasks']) }}</h2>
                        <small>{{ $processType == 'all' ? 'Semua jalur' : ($processType == 'normal' ? 'Jalur Normal' : 'Jalur Fasttrack') }}</small>
                    </div>
                    <i class="bi bi-{{ $config['icon'] }} fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total {{ $isMarketing ? 'Marketing' : 'PIC' }}</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($stats['total_pic']) }}</h2>
                        <small>{{ $isMarketing ? 'Marketing yang submit' : 'PIC yang melakukan tugas' }}</small>
                    </div>
                    <i class="bi bi-people-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Top Performer</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['top_pic'] ? $stats['top_pic']->pic_name : '-' }}</h2>
                        <small>{{ $stats['top_pic'] ? number_format($stats['top_pic']->total_task) . ($isMarketing ? ' submission' : ' tugas') : '' }}</small>
                    </div>
                    <i class="bi bi-trophy-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Ranking Table --}}
<div class="card">
    <div class="card-header bg-{{ $config['color'] }} text-white">
        <i class="bi bi-trophy-fill"></i> Peringkat Tim {{ $config['title'] }} Terbanyak
        @if($processType != 'all')
            <span class="badge bg-light text-dark ms-2">{{ $processType == 'normal' ? 'Normal' : 'Fasttrack' }}</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 70px;">Rank</th>
                        <th>{{ $isMarketing ? 'Nama Marketing' : 'Nama PIC' }}</th>
                        <th class="text-center">{{ $isMarketing ? 'Total Submission' : 'Total Tugas' }}</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rankings as $item)
                    <tr class="{{ $item->rank <= 3 ? 'table-warning' : '' }}">
                        <td class="text-center">
                            @if($item->rank == 1)
                                <span class="badge bg-warning text-dark" style="font-size: 1.1rem;">
                                    <i class="bi bi-trophy-fill"></i> 1
                                </span>
                            @elseif($item->rank == 2)
                                <span class="badge bg-secondary" style="font-size: 1.1rem;">
                                    <i class="bi bi-award-fill"></i> 2
                                </span>
                            @elseif($item->rank == 3)
                                <span class="badge bg-danger" style="font-size: 1.1rem;">
                                    <i class="bi bi-award"></i> 3
                                </span>
                            @else
                                <span class="text-muted fw-bold">{{ $item->rank }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-{{ $config['color'] }} me-2">
                                    {{ strtoupper(substr($item->pic_name, 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $item->pic_name }}</strong>
                                    @if($item->pic && !$item->pic->is_active)
                                        <span class="badge bg-secondary ms-1">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $config['color'] }}" style="font-size: 1rem;">
                                {{ number_format($item->total_task) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success" style="font-size: 1rem;">
                                {{ number_format($item->completed_task ?? 0) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $percentage = $stats['total_tasks'] > 0 ? ($item->total_task / $stats['total_tasks']) * 100 : 0;
                            @endphp
                            <div class="progress" style="height: 20px; min-width: 100px;">
                                <div class="progress-bar bg-{{ $config['color'] }}" role="progressbar" 
                                     style="width: {{ $percentage }}%">
                                    {{ number_format($percentage, 1) }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mb-0">Belum ada data {{ $isMarketing ? 'submission' : 'tugas' }} {{ $config['title'] }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-muted">
        Total {{ $rankings->count() }} {{ $isMarketing ? 'Marketing yang submit artikel' : 'PIC yang melakukan tugas '.$config['title'] }}
    </div>
</div>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.avatar-circle.bg-warning {
    color: #000;
}
</style>
@endsection
