@extends('pic.layouts.app')

@section('title', 'Data Slot Jurnal')
@section('page-title', 'Data Slot Jurnal')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" 
                       placeholder="🔍 Cari nama jurnal atau publisher..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="journal_id" class="form-select">
                    <option value="">-- Semua Jurnal --</option>
                    @foreach($journals as $journal)
                        <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                            {{ Str::limit($journal->nama_jurnal, 35) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="year" class="form-select">
                    <option value="">-- Tahun --</option>
                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <select name="month" class="form-select">
                    <option value="">-- Bulan --</option>
                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $m)
                        <option value="{{ $i + 1 }}" {{ request('month') == ($i + 1) ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="{{ route('pic.journal-slots.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar3"></i> Daftar Slot Jurnal</span>
        <a href="{{ route('pic.journal-slots.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Slot
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Jurnal</th>
                        <th>Periode</th>
                        <th>Volume/Issue</th>
                        <th>Total Slot</th>
                        <th>Tersedia</th>
                        <th>Kode Slot</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slots as $slot)
                    <tr>
                        <td>{{ $loop->iteration + ($slots->currentPage() - 1) * $slots->perPage() }}</td>
                        <td>
                            <strong>{{ $slot->journalMaster->nama_jurnal ?? '-' }}</strong>
                            @if($slot->journalMaster && $slot->journalMaster->publisher)
                                <br><small class="text-muted"><i class="bi bi-building"></i> {{ $slot->journalMaster->publisher }}</small>
                            @endif
                            @if($slot->journalMaster && $slot->journalMaster->accreditation)
                                <span class="badge bg-info ms-1">{{ $slot->journalMaster->accreditation }}</span>
                            @endif
                        </td>
                        <td>{{ $slot->bulan }}/{{ $slot->tahun }}</td>
                        <td>Vol. {{ $slot->volume ?? '-' }} No. {{ $slot->nomor ?? '-' }}</td>
                        <td><span class="badge bg-primary">{{ $slot->jumlah_slot }}</span></td>
                        <td>
                            @php
                                $tersedia = $slot->jumlah_slot - $slot->slot_terpakai;
                            @endphp
                            @if($tersedia > 0)
                                <span class="badge bg-success">{{ $tersedia }}</span>
                            @else
                                <span class="badge bg-danger">Penuh</span>
                            @endif
                        </td>
                        <td>
                            <x-slot-link :journal-slot="$slot" guard="pic" />
                        </td>
                        <td>
                            <a href="{{ route('pic.journal-slots.edit', $slot) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('pic.journal-slots.destroy', $slot) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data slot</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $slots->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
