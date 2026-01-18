@extends('layouts.app')

@section('title', 'Monitoring Slot - ' . $appSettings['app_name'])
@section('page-title', 'Monitoring Slot')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row mb-4">
    <!-- Statistics Cards -->
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Total Slot</h6>
                        <h2 class="card-title mb-0">{{ $stats['total_slots'] }}</h2>
                    </div>
                    <i class="bi bi-calendar3 fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Slot Terpakai</h6>
                        <h2 class="card-title mb-0">{{ $stats['slots_terpakai'] }}</h2>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Slot Tersedia</h6>
                        <h2 class="card-title mb-0">{{ $stats['slots_tersedia'] }}</h2>
                    </div>
                    <i class="bi bi-box fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart"></i> Monitoring Penggunaan Slot</span>
                <a href="{{ route('admin.journal-slots.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form action="{{ route('admin.journal-slots.monitoring') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="journal_master_id" class="form-label">Filter Jurnal</label>
                            <select class="form-select" id="journal_master_id" name="journal_master_id">
                                <option value="">-- Semua Jurnal --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                        {{ $journal->nama_jurnal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select class="form-select" id="tahun" name="tahun">
                                <option value="">-- Semua Tahun --</option>
                                @for($y = date('Y') + 1; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.journal-slots.monitoring') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Slot List with Progress -->
                @forelse($slotStats as $item)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h6 class="mb-1">
                                    <a href="{{ route('admin.journal-slots.show', $item['slot']) }}">
                                        {{ $item['slot']->journalMaster->nama_jurnal }}
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    Vol. {{ $item['slot']->volume }} No. {{ $item['slot']->nomor }} - {{ $item['slot']->bulan }} {{ $item['slot']->tahun }}
                                </small><br>
                                <code>{{ $item['slot']->kode_slot }}</code>
                            </div>
                            <div class="col-md-5">
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-{{ $item['status'] }}" role="progressbar" style="width: {{ $item['percentage'] }}%">
                                        {{ $item['percentage'] }}%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <span class="badge bg-secondary">{{ $item['total_slots'] }} Total</span>
                                <span class="badge bg-warning">{{ $item['used_slots'] }} Terpakai</span>
                                <span class="badge bg-success">{{ $item['available_slots'] }} Tersedia</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> Tidak ada data slot yang ditemukan
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
