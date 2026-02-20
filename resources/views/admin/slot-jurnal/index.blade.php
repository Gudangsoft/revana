@extends('admin.layouts.app')

@section('title', 'Slot Jurnal')
@section('page-title', 'Slot Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-calendar-range fs-1 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">Total Slot</h6>
                                <h3 class="mb-0">{{ $stats['total_slots'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle fs-1 text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">Slot Tersedia</h6>
                                <h3 class="mb-0 text-success">{{ $stats['available_slots'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-x-circle fs-1 text-danger"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">Slot Penuh</h6>
                                <h3 class="mb-0 text-danger">{{ $stats['full_slots'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-file-earmark-text fs-1 text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">Total Submit</h6>
                                <h3 class="mb-0 text-info">{{ $stats['total_submissions'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-search"></i> Pencarian Slot Jurnal</h5>
        @include('partials.column-toggle', ['tableId' => 'dataTable', 'columns' => ['Kode LOA', 'Nama Jurnal', 'Penerbit', 'Jumlah Slot', 'Slot Terpakai', 'Sisa', 'Status'], 'columnOffset' => 1])
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.slot-jurnal.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-tag"></i> Cari Kode LOA
                    </label>
                    <input type="text" 
                           name="search_loa" 
                           class="form-control" 
                           placeholder="Masukkan kode LOA..."
                           value="{{ request('search_loa') }}"
                           autofocus>
                    <small class="text-muted">Contoh: LOA-2024-001</small>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-journal"></i> Jurnal
                    </label>
                    <select name="journal_id" class="form-select">
                        <option value="">-- Semua Jurnal --</option>
                        @foreach($journals as $journal)
                            <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                                {{ $journal->nama_jurnal }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold">
                        <i class="bi bi-funnel"></i> Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="full" {{ request('status') == 'full' ? 'selected' : '' }}>Penuh</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-sort-down"></i> Urutkan
                    </label>
                    <div class="input-group">
                        <select name="sort_by" class="form-select">
                            <option value="kode_slot" {{ request('sort_by') == 'kode_slot' ? 'selected' : '' }}>Kode Slot</option>
                            <option value="volume" {{ request('sort_by') == 'volume' ? 'selected' : '' }}>Volume</option>
                            <option value="nomor" {{ request('sort_by') == 'nomor' ? 'selected' : '' }}>Nomor</option>
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal</option>
                            <option value="jumlah_slot" {{ request('sort_by') == 'jumlah_slot' ? 'selected' : '' }}>Jumlah Slot</option>
                        </select>
                        <select name="sort_order" class="form-select" style="max-width: 80px;">
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>▲</option>
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>▼</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="{{ route('admin.slot-jurnal.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        @if(request()->hasAny(['search_loa', 'journal_id', 'status']))
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            Menampilkan hasil pencarian: 
            @if(request('search_loa'))
                <strong>LOA: "{{ request('search_loa') }}"</strong>
            @endif
            @if(request('journal_id'))
                <strong>Jurnal: {{ $journals->find(request('journal_id'))->nama_jurnal ?? '' }}</strong>
            @endif
            @if(request('status'))
                <strong>Status: {{ request('status') == 'available' ? 'Tersedia' : 'Penuh' }}</strong>
            @endif
            - Ditemukan <strong>{{ $slots->total() }}</strong> slot
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover table-bordered" id="dataTable">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 15%">
                            <i class="bi bi-tag"></i> Kode LOA
                        </th>
                        <th style="width: 25%">
                            <i class="bi bi-journal-bookmark"></i> Nama Jurnal
                        </th>
                        <th style="width: 15%">
                            <i class="bi bi-building"></i> Penerbit
                        </th>
                        <th style="width: 10%" class="text-center">
                            <i class="bi bi-bar-chart"></i> Jumlah Slot
                        </th>
                        <th style="width: 10%" class="text-center">
                            <i class="bi bi-check2-circle"></i> Slot Terpakai
                        </th>
                        <th style="width: 10%" class="text-center">
                            <i class="bi bi-hourglass-split"></i> Sisa
                        </th>
                        <th style="width: 10%" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slots as $index => $slot)
                    <tr>
                        <td class="text-center">{{ $slots->firstItem() + $index }}</td>
                        <td>
                            <strong class="text-primary">{{ $slot->kode_slot }}</strong>
                            @if($slot->journalMaster && $slot->journalMaster->accreditation)
                                <br>
                                <span class="badge bg-info mt-1">
                                    {{ $slot->journalMaster->accreditation }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $slot->journalMaster->nama_jurnal ?? '-' }}</strong>
                            @if($slot->journalMaster)
                                <br><small class="text-muted">{{ $slot->journalMaster->bidang_ilmu ?? '' }}</small>
                            @endif
                        </td>
                        <td>{{ $slot->journalMaster->penerbit ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6">{{ $slot->jumlah_slot }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info fs-6">{{ $slot->slot_terpakai }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                $sisa = $slot->jumlah_slot - $slot->slot_terpakai;
                                $badgeClass = $sisa > 0 ? 'success' : 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badgeClass }} fs-6">{{ $sisa }}</span>
                        </td>
                        <td class="text-center">
                            @if($slot->slot_terpakai >= $slot->jumlah_slot)
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Penuh
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Tersedia
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="mb-0 mt-2">
                                @if(request()->hasAny(['search_loa', 'journal_id', 'status']))
                                    Tidak ada slot yang sesuai dengan kriteria pencarian
                                @else
                                    Belum ada data slot jurnal
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.per-page-selector', ['paginator' => $slots])
    </div>
</div>
@endsection
