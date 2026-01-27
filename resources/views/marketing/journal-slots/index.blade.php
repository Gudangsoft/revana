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
                <div class="row g-3 mb-2">
                    <div class="col-md-4">
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
                </div>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Tahun</label>
                        <input type="number" name="tahun" class="form-control" value="{{ request('tahun') }}" placeholder="2026">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            <a href="{{ route('marketing.journal-slots.index') }}" class="btn btn-outline-secondary" title="Reset">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </div>
                    </div>
                    @if(request()->hasAny(['search', 'akreditasi', 'kategori', 'jenis', 'bulan', 'tahun']))
                    <div class="col-md-8">
                        <label class="form-label small mb-1">&nbsp;</label>
                        <div>
                            <span class="badge bg-info">
                                <i class="bi bi-funnel"></i> Filter aktif: {{ collect(request()->only(['search', 'akreditasi', 'kategori', 'jenis', 'bulan', 'tahun']))->filter()->count() }} kriteria
                            </span>
                        </div>
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
                            <th style="width: 50px;">No</th>
                            <th>Jurnal</th>
                            <th>Publisher</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th>Volume</th>
                            <th>Nomor</th>
                            <th>Bulan/Tahun</th>
                            <th>Slot</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slots as $index => $slot)
                        @php
                            $slotTersedia = $slot->jumlah_slot - $slot->slot_terpakai;
                            $percentage = $slot->jumlah_slot > 0 ? ($slot->slot_terpakai / $slot->jumlah_slot) * 100 : 0;
                        @endphp
                        <tr>
                            <td>{{ $slots->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $slot->journalMaster->nama_jurnal ?? '-' }}</strong>
                                @if($slot->journalMaster->accreditation)
                                    <br><small class="badge bg-success">{{ $slot->journalMaster->accreditation }}</small>
                                @endif
                            </td>
                            <td><small>{{ $slot->journalMaster->publisher ?? '-' }}</small></td>
                            <td>
                                @if($slot->journalMaster->kategori)
                                    <span class="badge bg-info">{{ $slot->journalMaster->kategori }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($slot->journalMaster->jenis_jurnal)
                                    <span class="badge bg-primary">{{ $slot->journalMaster->jenis_jurnal }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">Vol. {{ $slot->volume }}</span></td>
                            <td><span class="badge bg-secondary">No. {{ $slot->nomor }}</span></td>
                            <td>{{ $slot->bulan }} {{ $slot->tahun }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 100px; height: 20px;">
                                        <div class="progress-bar {{ $percentage >= 80 ? 'bg-danger' : ($percentage >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                             role="progressbar" 
                                             style="width: {{ $percentage }}%"
                                             aria-valuenow="{{ $slot->slot_terpakai }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="{{ $slot->jumlah_slot }}">
                                        </div>
                                    </div>
                                    <small>
                                        <strong>{{ $slot->slot_terpakai }}</strong> / {{ $slot->jumlah_slot }}
                                    </small>
                                </div>
                                @if($slotTersedia > 0)
                                    <small class="text-success">
                                        <i class="bi bi-check-circle-fill"></i> {{ $slotTersedia }} tersedia
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="bi bi-x-circle-fill"></i> Penuh
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($slot->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2 mb-0">Tidak ada data slot</p>
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
