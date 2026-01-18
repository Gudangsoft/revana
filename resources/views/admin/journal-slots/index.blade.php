@extends('layouts.app')

@section('title', 'Data Slot - ' . $appSettings['app_name'])
@section('page-title', 'Data Slot')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar3"></i> Data Slot</span>
                <div>
                    <a href="{{ route('admin.journal-slots.monitoring') }}" class="btn btn-info">
                        <i class="bi bi-bar-chart"></i> Monitoring Slot
                    </a>
                    <a href="{{ route('admin.journal-slots.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Slot
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Filter -->
                <form action="{{ route('admin.journal-slots.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="journal_master_id" class="form-label">Jurnal</label>
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
                            <label for="bulan" class="form-label">Bulan</label>
                            <select class="form-select" id="bulan" name="bulan">
                                <option value="">-- Semua Bulan --</option>
                                @foreach($bulanOptions as $key => $value)
                                    <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select class="form-select" id="tahun" name="tahun">
                                <option value="">-- Semua Tahun --</option>
                                @for($y = date('Y') + 1; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.journal-slots.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Slot</th>
                                <th>Nama Jurnal</th>
                                <th>Publisher</th>
                                <th>Volume</th>
                                <th>Nomor</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>Jumlah Slot</th>
                                <th>Terpakai</th>
                                <th>Tersedia</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slots as $slot)
                            <tr>
                                <td>{{ $loop->iteration + ($slots->currentPage() - 1) * $slots->perPage() }}</td>
                                <td><code>{{ $slot->kode_slot }}</code></td>
                                <td>
                                    <a href="{{ route('admin.journal-masters.show', $slot->journalMaster) }}">
                                        {{ Str::limit($slot->journalMaster->nama_jurnal, 30) }}
                                    </a>
                                </td>
                                <td>{{ Str::limit($slot->journalMaster->publisher, 20) }}</td>
                                <td>{{ $slot->volume }}</td>
                                <td>{{ $slot->nomor }}</td>
                                <td>{{ $slot->bulan }}</td>
                                <td>{{ $slot->tahun }}</td>
                                <td><span class="badge bg-secondary">{{ $slot->jumlah_slot }}</span></td>
                                <td><span class="badge bg-warning">{{ $slot->slot_terpakai }}</span></td>
                                <td><span class="badge bg-success">{{ $slot->slot_tersedia }}</span></td>
                                <td>
                                    @if($slot->is_full)
                                        <span class="badge bg-danger">Penuh</span>
                                    @elseif($slot->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.journal-slots.show', $slot) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.journal-slots.edit', $slot) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.journal-slots.toggle-active', $slot) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $slot->is_active ? 'btn-secondary' : 'btn-success' }}" title="{{ $slot->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="bi {{ $slot->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.journal-slots.destroy', $slot) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus slot ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data slot
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $slots->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
