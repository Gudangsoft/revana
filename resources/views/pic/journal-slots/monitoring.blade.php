@extends('pic.layouts.app')

@section('title', 'Monitoring Slot')
@section('page-title', 'Monitoring Slot Jurnal')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="journal_id" class="form-select">
                    <option value="">Semua Jurnal</option>
                    @foreach($journals as $journal)
                        <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                            {{ $journal->nama_jurnal }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Cari Jurnal/Publisher" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="accreditation" class="form-select">
                    <option value="">Semua Akreditasi</option>
                    @foreach($accreditations as $accreditation)
                        <option value="{{ $accreditation->name }}" {{ request('accreditation') == $accreditation->name ? 'selected' : '' }}>{{ $accreditation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="year" class="form-select">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('pic.journal-slots.monitoring') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-bar-chart"></i> Monitoring Pengisian Slot
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Jurnal</th>
                        <th>Publisher</th>
                        <th>Akreditasi</th>
                        <th>Periode</th>
                        <th>Volume/Issue</th>
                        <th>Kapasitas</th>
                        <th>Progress</th>
                        <th>Kode Slot</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slots as $slot)
                    @php
                        $tersedia = $slot->jumlah_slot - $slot->slot_terpakai;
                        $percent = $slot->jumlah_slot > 0 ? round(($slot->slot_terpakai / $slot->jumlah_slot) * 100) : 0;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration + ($slots->currentPage() - 1) * $slots->perPage() }}</td>
                        <td><strong>{{ $slot->journalMaster->nama_jurnal ?? '-' }}</strong></td>
                        <td>{{ $slot->journalMaster->publisher ?? '-' }}</td>
                        <td>
                            @if($slot->journalMaster && $slot->journalMaster->accreditation)
                                <span class="badge bg-info">{{ $slot->journalMaster->accreditation }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $slot->bulan }}/{{ $slot->tahun }}</td>
                        <td>Vol. {{ $slot->volume ?? '-' }} No. {{ $slot->nomor ?? '-' }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $slot->slot_terpakai }}/{{ $slot->jumlah_slot }}</span>
                            <small class="text-muted">({{ $tersedia }} tersedia)</small>
                        </td>
                        <td style="min-width: 150px;">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $percent >= 100 ? 'bg-danger' : ($percent >= 75 ? 'bg-warning' : 'bg-success') }}" 
                                     role="progressbar" style="width: {{ $percent }}%">
                                    {{ $percent }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <x-slot-link :journal-slot="$slot" guard="pic" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Belum ada data slot</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @include('partials.per-page-selector', ['paginator' => $slots])
    </div>
</div>
@endsection
