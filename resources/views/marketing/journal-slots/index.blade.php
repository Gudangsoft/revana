@extends('marketing.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-calendar3"></i> Data Slot Jurnal</h4>
    </div>

    <!-- Filter -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('marketing.journal-slots.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Cari Jurnal / Publisher</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="🔍 Ketik nama jurnal atau publisher..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Akreditasi</label>
                        <select name="akreditasi" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="Sinta 1" {{ request('akreditasi') == 'Sinta 1' ? 'selected' : '' }}>Sinta 1</option>
                            <option value="Sinta 2" {{ request('akreditasi') == 'Sinta 2' ? 'selected' : '' }}>Sinta 2</option>
                            <option value="Sinta 3" {{ request('akreditasi') == 'Sinta 3' ? 'selected' : '' }}>Sinta 3</option>
                            <option value="Sinta 4" {{ request('akreditasi') == 'Sinta 4' ? 'selected' : '' }}>Sinta 4</option>
                            <option value="Sinta 5" {{ request('akreditasi') == 'Sinta 5' ? 'selected' : '' }}>Sinta 5</option>
                            <option value="Sinta 6" {{ request('akreditasi') == 'Sinta 6' ? 'selected' : '' }}>Sinta 6</option>
                            <option value="Non Sinta" {{ request('akreditasi') == 'Non Sinta' ? 'selected' : '' }}>Non Sinta</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Kategori</label>
                        <select name="kategori" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="Penelitian" {{ request('kategori') == 'Penelitian' ? 'selected' : '' }}>Penelitian</option>
                            <option value="PKM" {{ request('kategori') == 'PKM' ? 'selected' : '' }}>PKM</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Jenis</label>
                        <select name="jenis" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="Jurnal Nasional" {{ request('jenis') == 'Jurnal Nasional' ? 'selected' : '' }}>Nasional</option>
                            <option value="Jurnal Internasional" {{ request('jenis') == 'Jurnal Internasional' ? 'selected' : '' }}>Internasional</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Bulan</label>
                        <select name="bulan" class="form-select">
                            <option value="">-- Semua --</option>
                            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $month)
                                <option value="{{ $month }}" {{ request('bulan') == $month ? 'selected' : '' }}>{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small mb-1">Tahun</label>
                        <input type="number" name="tahun" class="form-control" value="{{ request('tahun') }}" placeholder="2026">
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('marketing.journal-slots.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                    @if(request()->hasAny(['search', 'akreditasi', 'kategori', 'jenis', 'bulan', 'tahun']))
                    <div class="col-auto">
                        <span class="badge bg-info py-2 px-3">
                            <i class="bi bi-funnel"></i> {{ collect(request()->only(['search', 'akreditasi', 'kategori', 'jenis', 'bulan', 'tahun']))->filter()->count() }} filter aktif
                        </span>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Kode Slot</th>
                            <th>Nama Jurnal</th>
                            <th>Publisher</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th>Akreditasi</th>
                            <th>Volume</th>
                            <th>Nomor</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Jumlah Slot</th>
                            <th>Terpakai</th>
                            <th>Tersedia</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slots as $slot)
                        <tr>
                            <td>{{ $loop->iteration + ($slots->currentPage() - 1) * $slots->perPage() }}</td>
                            <td><code>{{ $slot->kode_slot }}</code></td>
                            <td>{{ Str::limit($slot->journalMaster->nama_jurnal, 30) }}</td>
                            <td>{{ Str::limit($slot->journalMaster->publisher, 20) }}</td>
                            <td>
                                @if($slot->journalMaster->kategori)
                                    <span class="badge bg-{{ $slot->journalMaster->kategori == 'Penelitian' ? 'primary' : 'success' }}">{{ $slot->journalMaster->kategori }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($slot->journalMaster->jenis_jurnal)
                                    <span class="badge bg-{{ $slot->journalMaster->jenis_jurnal == 'Jurnal Internasional' ? 'warning' : 'secondary' }}">
                                        {{ $slot->journalMaster->jenis_jurnal == 'Jurnal Internasional' ? 'Internasional' : 'Nasional' }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($slot->journalMaster->accreditation)
                                    <span class="badge bg-info">{{ $slot->journalMaster->accreditation }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
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
                            <td class="text-center">
                                @if($slot->journalMaster && $slot->journalMaster->link_jurnal)
                                    <a href="{{ $slot->journalMaster->link_jurnal }}" target="_blank" class="btn btn-sm btn-info" title="Lihat Jurnal">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="16" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada data slot
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $slots->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
